local cjson = require "cjson.safe"
local utils = require "kong.plugins.shared.utils"
local i18n = require "kong.plugins.shared.i18n"

local CustomErrorHandler = {
  PRIORITY = 900,
  VERSION  = "1.0.0",
}

-- ============================================================================
-- Error Detection Logic
-- ============================================================================

local function detect_jwt_error_code(original_body)
  if not original_body or original_body == "" then
    return "unauthorized"
  end

  local success, original_data = pcall(cjson.decode, original_body)
  if not success or not original_data then
    return "unauthorized"
  end

  local error_result = "Unauthorized"
  if original_data.message then
    error_result = original_data.message
  elseif original_data.exp then
    error_result = original_data.exp
  end

  local msg = string.lower(error_result)

  -- Check for specific JWT error patterns
  if msg:find("jwt expired") or msg:find("token expired") or msg:find("'exp' claim") then
    return "token_expired"
  elseif msg:find("invalid signature") then
    return "invalid_signature"
  elseif msg:find("invalid claims") then
    return "invalid_claims"
  elseif msg:find("no jwt found") or msg:find("missing") then
    return "missing_token"
  elseif msg:find("invalid") or msg:find("malformed") then
    return "invalid_token"
  elseif msg:find("unauthorized") then
    if kong.request.get_header("Authorization") then
      return "invalid_token"
    else
      return "required_token"
    end
  end

  return "unauthorized"
end

-- ============================================================================
-- Plugin Phases
-- ============================================================================

function CustomErrorHandler:header_filter(conf)
  local status = kong.response.get_status()
  local req_id = utils.ensure_request_id_response()

  if status == 401 or status == 403 then
    kong.response.clear_header("Content-Length")
    kong.response.clear_header("WWW-Authenticate")
    kong.response.set_header("Content-Type", "application/json; charset=utf-8")
  end
end

function CustomErrorHandler:body_filter(conf)
  local status = kong.response.get_status()

  if status ~= 401 and status ~= 403 then
    return
  end

  local chunk = ngx.arg[1]
  local eof = ngx.arg[2]
  local ctx = ngx.ctx

  -- Buffer the original error body
  if not ctx.custom_error_buffer then
    ctx.custom_error_buffer = ""
  end

  if chunk then
    ctx.custom_error_buffer = ctx.custom_error_buffer .. chunk
  end

  -- Process when end of file is reached
  if eof then
    local code_name = detect_jwt_error_code(ctx.custom_error_buffer)
    local error_msg = i18n.get_error_message(code_name)

    local auth_header = kong.request.get_header("Authorization")
    local token = utils.extract_bearer_token(auth_header)
    local body = utils.build_error_response(status, code_name, error_msg, token)

    ngx.arg[1] = body
  else
    ngx.arg[1] = nil
  end
end

return CustomErrorHandler

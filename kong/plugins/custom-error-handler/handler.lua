local cjson = require "cjson.safe"
local utils = require "kong.plugins.shared.utils"
local i18n = require "kong.plugins.shared.i18n"

local CustomErrorHandler = {
  PRIORITY = 900,
  VERSION  = "1.0.0",
}

-- ============================================================================
-- Global buffer untuk menyimpan body sementara
-- ============================================================================
local response_body_buffer = {}

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
-- Helper: Cek apakah response sudah memiliki struktur Laravel 12
-- ============================================================================
local function is_already_normalized_response(original_body)
  if not original_body or original_body == "" then
    return false
  end

  local success, original_data = pcall(cjson.decode, original_body)
  if not success or not original_data then
    return false
  end

  if original_data.error and type(original_data.error) == "table" then
    local error_obj = original_data.error
    if error_obj.status_code and error_obj.code_name then
      return true
    end
  end

  return false
end

-- ============================================================================
-- Plugin Phases
-- ============================================================================

function CustomErrorHandler:access(conf)
  -- Inisialisasi buffer DI AWAL untuk hindari timing issue
  local request_id = ngx.var.request_id or kong.request.get_header("X-Request-ID")
  if request_id then
    response_body_buffer[request_id] = ""
    ngx.ctx.request_id = request_id  -- Simpan di context untuk mudah akses
  end
end

function CustomErrorHandler:header_filter(conf)
  local status = kong.response.get_status()
  local req_id = utils.ensure_request_id_response()

  if status == 401 or status == 403 then
    -- Gunakan request_id dari context atau variable
    local request_id = ngx.ctx.request_id or ngx.var.request_id
    local body = request_id and response_body_buffer[request_id]

    --!! Log untuk debugging timing
    kong.log.debug("CustomErrorHandler: header_filter - buffer length: ", body and #body or 0)

    -- Jika ada body di buffer dan sudah format Laravel 12, skip modifikasi header
    if body and body ~= "" and is_already_normalized_response(body) then
      kong.log.debug("CustomErrorHandler: Laravel response detected, skipping header mod")
      return
    end

    -- Default: modifikasi header untuk Kong JWT atau buffer kosong
    kong.log.debug("CustomErrorHandler: Kong JWT or empty buffer, modifying headers")
    kong.response.clear_header("Content-Length")
    kong.response.clear_header("WWW-Authenticate")
    kong.response.set_header("Content-Type", "application/json; charset=utf-8")

    -- Set flag di context untuk body_filter
    ngx.ctx.should_normalize = true
  else
    ngx.ctx.should_normalize = false
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
    -- Tambah ke buffer local
    ctx.custom_error_buffer = ctx.custom_error_buffer .. chunk

    -- Update buffer global UNTUK REQUEST BERIKUTNYA
    -- (header_filter untuk request ini sudah lewat)
    local request_id = ngx.ctx.request_id or ngx.var.request_id
    if request_id then
      response_body_buffer[request_id] = ctx.custom_error_buffer
    end
  end

  -- Process when end of file is reached
  if eof then
    -- Gunakan flag dari header_filter atau cek lagi
    local should_normalize = ngx.ctx.should_normalize or false

    -- Jika header_filter sudah set flag false (Laravel response)
    if should_normalize == false then
      ngx.arg[1] = ctx.custom_error_buffer
    else
      -- Cek lagi untuk pastikan
      if is_already_normalized_response(ctx.custom_error_buffer) then
        ngx.arg[1] = ctx.custom_error_buffer
      else
        local code_name = detect_jwt_error_code(ctx.custom_error_buffer)
        local error_msg = i18n.get_error_message(code_name)

        local auth_header = kong.request.get_header("Authorization")
        local token = utils.extract_bearer_token(auth_header)
        local body = utils.build_error_response(status, code_name, error_msg, token)

        ngx.arg[1] = body
      end
    end

    -- Cleanup
    local request_id = ngx.ctx.request_id or ngx.var.request_id
    if request_id then
      response_body_buffer[request_id] = nil
    end
    ctx.custom_error_buffer = nil
  else
    ngx.arg[1] = nil
  end
end

function CustomErrorHandler:log(conf)
  -- Final cleanup
  local request_id = ngx.ctx.request_id or ngx.var.request_id
  if request_id then
    response_body_buffer[request_id] = nil
  end
end

return CustomErrorHandler

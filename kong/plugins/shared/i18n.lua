local M = {}

-- Error messages in multiple languages
local MESSAGES = {
  en = {
    -- Rate limit errors
    rate_limit_exceeded = "Too many requests. Please try again later.",

    -- JWT errors
    unauthorized = "Unauthorized access",
    token_expired = "Token has expired",
    invalid_signature = "Invalid token signature",
    invalid_claims = "Invalid token claims",
    invalid_token = "Invalid authentication token",
    required_token = "Authentication token is required",
    missing_token = "Authentication token is missing",
  },
  id = {
    -- Rate limit errors
    rate_limit_exceeded = "Terlalu banyak permintaan. Silakan coba lagi nanti.",

    -- JWT errors
    unauthorized = "Akses tidak sah",
    token_expired = "Token telah kedaluwarsa",
    invalid_signature = "Tanda tangan token tidak valid",
    invalid_claims = "Klaim token tidak valid",
    invalid_token = "Token autentikasi tidak valid",
    required_token = "Token autentikasi diperlukan",
    missing_token = "Token autentikasi tidak ditemukan",
  }
}

-- Translate message based on language and code
function M.translate(lang, code_name)
  local lang_messages = MESSAGES[lang] or MESSAGES["en"]
  return lang_messages[code_name] or lang_messages["unauthorized"]
end

-- Get translated error message with language detection
function M.get_error_message(code_name)
  local utils = require "kong.plugins.shared.utils"
  local lang = utils.detect_lang()
  return M.translate(lang, code_name)
end

return M

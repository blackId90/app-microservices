local typedefs = require "kong.db.schema.typedefs"

return {
  name = "custom-cors",
  fields = {
    { protocols = typedefs.protocols_http },
    {
      config = {
        type = "record",
        fields = {
          {
            enabled = {
              type = "boolean",
              default = true,
              description = "Enable/disable CORS"
            }
          },
          {
            origins = {
              type = "array",
              elements = { type = "string" },
              description = "Allowed origins (* for all)"
            }
          },
          {
            methods = {
              type = "array",
              default = { "GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS" },
              elements = {
                type = "string",
                one_of = { "GET", "HEAD", "PUT", "PATCH", "POST", "DELETE", "OPTIONS", "TRACE", "CONNECT" }
              },
              description = "Allowed HTTP methods"
            }
          },
          {
            headers = {
              type = "array",
              elements = { type = "string" },
              description = "Allowed request headers"
            }
          },
          {
            exposed_headers = {
              type = "array",
              elements = { type = "string" },
              description = "Headers exposed to client"
            }
          },
          {
            credentials = {
              type = "boolean",
              default = false,
              description = "Allow credentials (cookies, authorization headers)"
            }
          },
          {
            max_age = {
              type = "number",
              default = 3600,
              description = "Preflight cache duration in seconds"
            }
          },
          {
            preflight_continue = {
              type = "boolean",
              default = false,
              description = "Continue to next plugin after preflight"
            }
          },
        },
      },
    },
  },
}

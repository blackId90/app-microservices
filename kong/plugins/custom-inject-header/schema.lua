local typedefs = require "kong.db.schema.typedefs"

return {
  name = "custom-inject-header",
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
              description = "Enable/disable header injection"
            }
          },
          {
            headers = {
              type = "array",
              required = true,
              elements = {
                type = "record",
                fields = {
                  {
                    name = {
                      type = "string",
                      required = true,
                      description = "Name of the header to inject"
                    }
                  },
                  {
                    generator = {
                      type = "string",
                      default = "uuid",
                      one_of = { "uuid", "uuid#counter", "tracker", "timestamp", "epoch" },
                      description = "Generator type for the header value"
                    }
                  },
                  {
                    echo_downstream = {
                      type = "boolean",
                      default = true,
                      description = "Include the header in the response"
                    }
                  },
                  {
                    override_existing = {
                      type = "boolean",
                      default = false,
                      description = "Override header if it already exists"
                    }
                  },
                },
              },
            },
          },
        },
      },
    },
  },
}

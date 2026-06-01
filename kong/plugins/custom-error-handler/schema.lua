local typedefs = require "kong.db.schema.typedefs"

return {
  name = "custom-error-handler",
  fields = {
    { protocols = typedefs.protocols_http },
    {
      config = {
        type = "record",
        fields = {
          -- Add configuration fields if needed in the future
          { enabled = { type = "boolean", default = true } },
        },
      },
    },
  },
}

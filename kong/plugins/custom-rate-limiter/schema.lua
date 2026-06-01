return {
  name = "custom-rate-limiter",
  fields = {
    {
      config = {
        type = "record",
        fields = {
          {
            redis_host = {
              type = "string",
              default = "redis-local",
              description = "Redis host address"
            }
          },
          {
            redis_port = {
              type = "number",
              default = 6379,
              description = "Redis port"
            }
          },
          {
            redis_password = {
              type = "string",
              description = "Redis password (optional)"
            }
          },
          {
            redis_database = {
              type = "number",
              default = 0,
              description = "Redis database index"
            }
          },
          {
            redis_timeout = {
              type = "number",
              default = 2000,
              description = "Redis connection timeout in milliseconds"
            }
          },
          {
            limit_by_ip = {
              type = "number",
              default = 100,
              description = "Rate limit for unauthenticated requests (by IP)"
            }
          },
          {
            limit_by_user = {
              type = "number",
              default = 1000,
              description = "Rate limit for authenticated requests (by JTI)"
            }
          },
          {
            window_size = {
              type = "number",
              default = 60,
              description = "Time window in seconds"
            }
          },
          {
            enabled = {
              type = "boolean",
              default = true,
              description = "Enable/disable rate limiting"
            }
          },
        },
      },
    },
  },
}

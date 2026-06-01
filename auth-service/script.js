import http from "k6/http";
import { check, sleep } from "k6";
import { Rate } from "k6/metrics";

// Custom metrics
const errorRate = new Rate("errors");

// Test configuration
export const options = {
    stages: [
        { duration: "10s", target: 50 }, // Ramp up to 50 VUs over 10s
        { duration: "50s", target: 100 }, // Stay at 100 VUs for 50s
        { duration: "10s", target: 0 }, // Ramp down to 0 VUs over 10s
    ],
    thresholds: {
        http_req_duration: ["p(95)<200"], // 95th percentile should be below 200ms
        http_req_failed: ["rate<0.1"], // Error rate should be below 10%
        errors: ["rate<0.1"], // Custom error rate should be below 10%
    },
};

// API endpoint configuration
const BASE_URL = "http://127.0.0.1:8000";
const API_ENDPOINT = "api/v1";
const PATHNAME = 'user'
const BEARER_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvYXBpL3YxL2F1dGgvbG9naW4iLCJpYXQiOjE3NjA3MjYwMjMsImV4cCI6MTc2MDcyOTYyMywibmJmIjoxNzYwNzI2MDIzLCJqdGkiOiJqd3RfNjhmMjhjMDdkNjdlNzAuNDUwMTk5NTQiLCJzdWIiOiJhZTJjM2UxNy02NDk0LTExZWQtYmNkMS0xODYwMjRiOGQxNzQiLCJwcnYiOiIzZDNhNjg3OTYyMTMzYzA5YjVlYTkwNTM1ZTBiMzFiYmM4OWIyMDIwIn0.SJcH3sAhjNhre90czElBKd6ck--RJVLxW-XyboqZ2fI"

export default function () {
    // Make HTTP GET request to the API endpoint
    const response = http.get(`${BASE_URL}/${API_ENDPOINT}/${PATHNAME}`, {
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "Authorization": `Bearer ${BEARER_TOKEN}`
        },
    });

    // Check response status and validate response
    const result = check(response, {
        "status is 200": (r) => r.status === 200,
        "response time < 200ms": (r) => r.timings.duration < 200,
        "response has body": (r) => r.body.length > 0,
        "content type is JSON": (r) => r.headers["Content-Type"]?.includes("application/json"),
    });

    // Log failed responses
    /*
    if (!result) {
        console.error(`❌ Request failed with status: ${response.status}`);
        console.error(`❌ Response body: ${response.body}`);
    }
    */

    // Record errors for custom metric
    errorRate.add(!result);

    // Optional: Parse and validate JSON response structure
    try {
        const jsonResponse = JSON.parse(response.body);

        //!! Additional checks for API response structure (customize based on your API)
        check(jsonResponse, {
            "status is 200": (obj) => obj.status === 200,
            "message is Success": (obj) => obj.message === "Success",
            "data field exists": (obj) => typeof obj.data === "object" && obj.data !== null,
        });

        /*
        if (!jsonCheck) {
            console.error(`❌ JSON validation failed: ${JSON.stringify(jsonResponse)}`);
        }
        */
    } catch (e) {
        console.error(`Failed to parse JSON response: ${e}`);
        errorRate.add(true);
    }

    // Add small delay between requests to simulate real user behavior
    sleep(1);
}

// Setup function (runs once at the beginning)
export function setup() {
    console.log("Starting K6 performance test for Laravel 12 A RESTful API");
    console.log(`Target URL: ${BASE_URL}/${API_ENDPOINT}/${PATHNAME}`);
    console.log("Test stages:");
    console.log("- 10s: Ramp up to 50 VUs");
    console.log("- 50s: Maintain 100 VUs");
    console.log("- 10s: Ramp down to 0 VUs");
    console.log("Performance threshold: 95th percentile < 200ms");

    // Optional: Verify API is accessible before starting the test
    const healthCheck = http.get(`${BASE_URL}/${API_ENDPOINT}/${PATHNAME}`, {
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "Authorization": `Bearer ${BEARER_TOKEN}`
        },
    });
    if (healthCheck.status !== 200) {
        throw new Error(`API health check failed. Status: ${healthCheck.status}`);
    }

    return { timestamp: new Date().toISOString() };
}

// Teardown function (runs once at the end)
export function teardown(data) {
    console.log(`K6 performance test completed at: ${new Date().toISOString()}`);
    console.log(`Test started at: ${data.timestamp}`);
}

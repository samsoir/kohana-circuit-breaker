# Kohana Circuit Breaker

Circuit Breaker provides a way of gracefully managing external services by monitoring and recording connections that repeatedly fail or timeout. The Circuit Breaker will break the connection if a service continually fails to return correct information, allowing application logic to gracefully handle the degradation in service.

Used in conjunction with the Kohana Request class, Circuit Breaker provides a robust tool for ensuring QA throughout your application.
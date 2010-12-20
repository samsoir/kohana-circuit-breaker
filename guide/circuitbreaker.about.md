# About Circuit Breaker

Circuit Breaker provides a simple API for robustly managing external services from within Kohana. This enables software to gracefully handle failing services and provide alternate content or logic for situations where services are unavailable for whatever reason. To this end, Circuit Breaker provides the following functionality:

 - Tracks connection status by name
   - Names are arbitrary
   - By default circuits are all closed
 - Provides a simple interface for testing opened circuits
 - Very simple interface

Circuit Breaker will not automatically manage connections for you. This is an important point to raise as the logic around opening and closing connections is purely down to implementation.

## Glossary

__Term__           | _Definition_
------------------ | -----------------------------------------
__Circuit__        | A connection to a component, internal or external. Usually this would be an external service; i.e. Twitter API
__Open Circuit__   | The circuit breaker is open, therefore the circuit is broken and nothing can flow through the circuit. This signifies a __fail__ state.
__Closed Circuit__ | The circuit breaker is closed, therefore the circuit is complete and information can flow. This signifies the __normal__ state.

### This is a new header!

Baljdsh  asjhdjsh js sjdh sj
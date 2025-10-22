# Chat App AI Workflow

> **Architectural Note:** The detailed AI workflow described below (Collaboration, Analysis, Breakdown, etc.) is executed on the **frontend**. The frontend application orchestrates this logic and interacts with a set of dedicated backend APIs to perform specific tasks like calling a foundational AI model, executing database queries, and saving conversation history.

---

## Frontend AI Workflow

### [1] Collaboration AI
- **Purpose**: To clarify the user's needs and generate a precise initial context for the subsequent AI agents.
- **Trigger**: A new user request is initiated.
- **Inputs**:
    - The user's initial, often ambiguous, prompt (e.g., "Find my best employees").
- **Core Logic**:
    - Engages in a natural language dialogue to deconstruct the request.
    - Asks targeted questions to resolve ambiguity (e.g., "What are the criteria for 'best'? Performance reviews, sales figures, or tenure?").
    - Confirms the scope, constraints, and desired output format with the user.
    - Synthesizes the entire conversation into a structured and unambiguous task description.
- **Output**:
    - A detailed context object for the Analysis AI.
    - A `[[COLLAB_DONE]]` trigger to advance the workflow.
- **Example**:
    - **User**: "I need a list of inactive users."
    - **AI**: "Certainly. How would you like to define 'inactive'? For example, users who haven't logged in for the past 30 days?"
    - **User**: "Yes, 30 days is good. And I only want to see users from the 'enterprise' plan."
    - **Output Context**: `{ task: "find_inactive_users", criteria: { last_login: ">30 days ago", plan: "enterprise" }, output_format: "list" }`

---

### [2] Analysis AI
- **Purpose**: To summarize the user's intent and the clarified context into a concise brief for the Breakdown AI.
- **Trigger**: Automatic, upon receiving `[[COLLAB_DONE]]`.
- **Inputs**:
    - The structured context object from the Collaboration AI.
- **Core Logic**:
    - Parses the context object.
    - Identifies the core intent, key entities, and constraints.
    - Formulates a high-level summary of the task to be performed. This step ensures the core requirement is understood before planning the execution steps.
- **Output**:
    - A summarized intent string or object (e.g., "Task: Retrieve enterprise users inactive for >30 days.").

---

### [3] Breakdown AI
- **Purpose**: To produce an ordered, step-by-step workflow plan to accomplish the user's request.
- **Trigger**: Automatic, after the Analysis AI completes its summary.
- **Inputs**:
    - The summarized intent from the Analysis AI.
- **Core Logic**:
    - Decomposes the summarized task into a sequence of logical, executable steps.
    - Determines the dependencies between steps.
    - Structures the output as an ordered list of actions.
- **Output**:
    - An ordered list of workflow steps.
- **Example**:
    - **Input Intent**: "Task: Retrieve enterprise users inactive for >30 days."
    - **Output Steps**:
        1. `Find the 'enterprise' plan ID.`
        2. `Query the database for users matching the plan ID.`
        3. `Filter the results to include only users whose 'last_login' is older than 30 days.`
        4. `Format the final user list for display.`

---

### [4] Execution AI
- **Purpose**: To process each step from the workflow, deciding if it requires data retrieval (a query).
- **Trigger**: Automatic, processing one step at a time from the Breakdown AI's list.
- **Inputs**:
    - A single step from the workflow plan (e.g., "Find the 'enterprise' plan ID.").
- **Core Logic**:
    - Analyzes the step's action and determines if it can be resolved internally or if it needs to access an external data source (like a database).
    - If a database query is needed, it formulates the query and sends it to the **SQL Query Executor API**.
- **Output**:
    - If a query is needed: A `[[QUERY_REQUIRED]]` flag along with the formulated query.
    - If no query is needed: The result of the internal action.

---

### [4.1] Safety AI
- **Purpose**: To check the formulated query for safety, permissions, and potential risks before execution. This logic runs on the frontend before calling the backend API.
- **Trigger**: A `[[QUERY_REQUIRED]]` flag is detected from the Execution AI.
- **Inputs**:
    - The database query formulated by the Execution AI.
- **Core Logic**:
    - Scans the query for destructive commands (`DROP`, `DELETE`, `UPDATE` without a `WHERE` clause).
    - Validates against a set of allowed query patterns.
- **Output**:
    - `[[SAFE_TO_RUN]]` if the query passes all checks.
    - `[[UNSAFE]]` if the query is flagged as risky, along with a reason.

---

### [5] Query Executor
- **Purpose**: To call the backend API to execute a safe, structured query against the database.
- **Trigger**: Receiving the `[[SAFE_TO_RUN]]` signal.
- **Inputs**:
    - The validated database query.
- **Core Logic**:
    - Makes an HTTP request to the `/api/query-executor` backend endpoint, sending the query.
- **Output**:
    - The raw query result from the backend, or a database error message.

---

### [6] Verification & Correction AI
- **Purpose**: To validate the query result and handle any errors, suggesting corrections if necessary.
- **Trigger**: After the Query Executor returns its output.
- **Inputs**:
    - The query result or error message from the backend.
- **Core Logic**:
    - **On Success**: Performs a sanity check on the results.
    - **On Failure**: Analyzes the error message to diagnose the problem and attempts to formulate a corrected query for retry.
- **Output**:
    - If successful: `[[QUERY_VERIFIED]]` and the validated data.
    - If failed: A suggestion for a corrected query and a retry signal.

---

### [7] Execution AI (Resumed)
- **Purpose**: To continue processing the remaining steps in the workflow.
- **Trigger**: Receiving the `[[QUERY_VERIFIED]]` signal.
- **Inputs**:
    - The next step from the workflow plan.
    - The data returned from the verified query.
- **Core Logic**:
    - Integrates the query results into its current state and proceeds to the next step.
- **Output**:
    - Continues the loop until all steps are completed.

---

### [8] Finalization AI
- **Purpose**: To aggregate all results and produce a final, user-facing summary or answer.
- **Trigger**: After the last workflow step is completed and verified.
- **Inputs**:
    - The accumulated results and data from all executed steps.
- **Core Logic**:
    - Synthesizes the information into a coherent and human-readable response.
- **Output**:
    - The final, polished response delivered to the user in the chat interface.

---
---

## Backend API Architecture

The frontend AI workflow is supported by three distinct backend APIs.

### 1. AI Service API

-   **Endpoint**: `/api/ai-service`
-   **Purpose**: To act as a simple, secure proxy to a foundational large language model (LLM), abstracting the direct AI provider interaction from the frontend.
-   **Request Body**:
    -   `context`: A JSON object or string providing the background for the conversation or task.
    -   `message`: The specific user prompt or internal message to be processed by the LLM.
-   **Process**:
    1.  Receives `context` and `message` from the frontend.
    2.  Constructs a request to the specified OpenAI model (`gpt-5-mini`).
    3.  Forwards the request to the OpenAI API.
    4.  Receives the response from OpenAI.
-   **Response Body**:
    -   Returns the raw JSON response from the OpenAI API directly to the frontend client.

### 2. SQL Query Executor API

-   **Endpoint**: `/api/query-executor`
-   **Purpose**: To safely execute SQL queries generated by the frontend AI logic against the application database.
-   **Request Body**:
    -   `query`: A string containing the SQL query to be executed.
-   **Process**:
    1.  Receives the `query` string from the frontend.
    2.  **Crucially, it performs a final safety check** to prevent destructive operations or SQL injection. It should only allow `SELECT` statements and validate the query structure as a secondary safeguard.
    3.  If the query is deemed safe, it executes it against the database.
    4.  If the query is unsafe, it returns an error.
-   **Response Body**:
    -   **On Success**: A JSON object containing the data retrieved by the query.
    -   **On Failure**: A JSON object with an error message, indicating either a safety violation or a database execution error.

### 3. Chat History API

-   **Endpoint**: `/api/chat-history`
-   **Purpose**: To persist and retrieve conversation history.
-   **Methods**:
    -   `POST`: Saves a new chat entry.
        -   **Request Body**: `{ userId: string, conversationId: string, entry: { role: 'user' | 'ai', message: string, timestamp: date } }`
        -   **Response**: `{ success: true, entryId: string }`
    -   `GET`: Retrieves the history for a specific conversation.
        -   **Query Parameters**: `?conversationId=<ID>`
        -   **Response**: An array of chat entries for the given conversation.
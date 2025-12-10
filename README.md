# Risk Control System

## Overview
The Risk Control System is a robust, real-time monitoring solution designed to evaluate trading activity against a set of configurable risk rules. It automatically detects anomalies, generates incidents, and can trigger protective actions (such as disabling trading or sending notifications) to mitigate financial risk.

## Architecture

The system follows a modern **Microservices-ready** architecture, separating the frontend user interface from the backend logic. This design ensures scalability, maintainability, and flexibility.

```mermaid
graph TD
    User[User / Admin] -->|HTTPS| Frontend[Frontend (Next.js)]
    Frontend -->|REST API| Backend[Backend (Laravel)]
    Backend -->|Read/Write| DB[(MySQL Database)]
    
    subgraph "Risk Evaluation Engine"
        TradeEvent[Trade Saved Event] -->|Trigger| Listener[EvaluateRiskListener]
        Listener -->|Call| Service[RiskEvaluationService]
        Service -->|Fetch| Rules[Active Risk Rules]
        Service -->|Evaluate| Trade[Trade Data]
        Service -->|Result| Incident[Create Incident if Violated]
    end
    
    Backend -.->|Async Event| TradeEvent
```

### Why this Architecture?
1.  **Separation of Concerns**: The **Next.js** frontend handles the presentation layer and user interaction, while the **Laravel** backend focuses on business logic, data validation, and risk evaluation. This allows frontend and backend teams to work independently.
2.  **Scalability**: The event-driven nature of the risk engine allows for high throughput. As trading volume grows, the risk evaluation can be offloaded to background queues without blocking the main API response.
3.  **Maintainability**: Risk rules are implemented as separate Strategy classes (Strategy Pattern), making it easy to add new rules without modifying the core evaluation logic.

---

## Risk Rules

The system implements a dynamic rule engine. Below are the three core rules recently added to enhance risk coverage:

### 1. Minimum Trade Duration (`min_duration`)
*   **Logic**: Calculates the duration of a closed trade (`close_time - open_time`). If the duration is less than `min_duration_seconds`, an incidence is generated.
*   **Importance**: This rule is critical for preventing **Scalping** or **High-Frequency Trading (HFT)** on platforms where such strategies are prohibited. It also helps detect latency arbitrage attempts.
*   **Configuration**:
    *   `min_duration_seconds`: The minimum allowed time (in seconds) a trade must remain open.

### 2. Volume Consistency (`volume_consistency`)
*   **Logic**: Calculates the average volume of the user's last `lookback_trades`. It then checks if the current trade's volume is within a safe range (`min_factor` to `max_factor` of the average).
*   **Importance**: This rule detects **"Fat-finger" errors** (accidentally entering a trade size 10x larger than intended) and **Gambling behavior** (suddenly increasing position size to recover losses). It ensures traders stick to a consistent sizing strategy.
*   **Configuration**:
    *   `lookback_trades`: Number of historical trades to average.
    *   `min_factor`: Minimum multiplier (e.g., 0.5x average).
    *   `max_factor`: Maximum multiplier (e.g., 2.0x average).

### 3. Trade Frequency (`trade_frequency`)
*   **Logic**: Counts the number of trades opened by the user within a rolling time window (`time_window_minutes`). If the count exceeds `max_open_trades` or is below `min_open_trades`, an incidence is generated.
*   **Importance**: This prevents **Churning** (excessive trading to generate commissions) and **Over-trading** (emotional trading). It can also detect bot activity if the frequency is inhumanly high.
*   **Configuration**:
    *   `time_window_minutes`: The rolling window to check (e.g., last 60 minutes).
    *   `max_open_trades`: Maximum allowed trades in that window.

**Note**: All rules can be enabled/disabled and configured via the database or UI without changing a single line of code.

---

## Execution Triggers

We have implemented an **Event-Based Evaluation** approach.

### Chosen Approach: Event-Driven (Real-Time)
Instead of waiting for a periodic cron job to run every few minutes, the system evaluates risk rules **immediately** when a trade is saved/closed.

*   **Implementation**: We use Laravel's Event system. When a trade is saved, a `TradeSaved` event is fired. The `EvaluateRiskListener` catches this event and immediately runs the `RiskEvaluationService`.
*   **Why this choice?**:
    *   **Immediate Mitigation**: In risk control, speed is paramount. If a user violates a "Max Daily Loss" or "Volume Consistency" rule, we need to know *instantly* to potentially disable their account or block further trading. A periodic check (e.g., every 5 minutes) leaves a dangerous gap where a rogue algorithm could drain an account.
    *   **Efficiency**: We only evaluate rules when there is actual activity (a trade), rather than polling the database constantly when no trading is happening.

---

## Installation & Execution

### Manual Installation

Follow these steps to run the application directly on your machine.




#### Prerequisites
*   PHP 8.2+
*   Composer
*   Node.js & npm
*   MySQL Server

#### Backend Setup
1.  Navigate to the backend directory:
    ```bash
    cd backend
    ```
2.  Install dependencies:
    ```bash
    composer install
    ```
3.  Configure Environment:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *   Edit `.env` and set your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4.  Run Migrations & Seeds:
    ```bash
    php artisan migrate --seed
    ```
5.  Start the Server:
    ```bash
    php artisan serve
    ```
    The API will be available at http://127.0.0.1:8000.

#### Frontend Setup
1.  Navigate to the frontend directory:
    ```bash
    cd frontend
    ```
2.  Install dependencies:
    ```bash
    npm install
    ```
3.  Start the Development Server:
    ```bash
    npm run dev
    ```
    The application will be available at http://localhost:3000.

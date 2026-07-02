 Financial Tracking App System Design (Scalable and Secure)

A financial application has one absolute rule: data integrity. A tiny error in the balance will destroy user trust immediately. This document details the architecture, database specifications, and scaling strategies to build a solid financial tracking system. We use a Modular Monolith approach. It keeps things centralized now but prepares you to split into microservices later.

## 1. Tech Stack Specifications

These choices focus on stability, security, and strict relational data handling.

* **Backend Framework:** PHP Laravel. The ecosystem is mature. It has solid OOP support, queue handling, and clean database abstraction.
* **Primary Database:** PostgreSQL. This is mandatory. It enforces strict ACID compliance and handles complex relational queries much better than MySQL.
* **Caching and Message Broker:** Redis. Use this for stateless sessions, fast balance caching, and background job queues.
* **Infrastructure and Deployment:** Ubuntu Server with Docker Containerization. Nginx acts as the web server and reverse proxy.

## 2. Software Architecture (Backend)

Do not mix financial business logic inside the Controller. Apply a strict separation of concerns using a three tier pattern.

[HTTP Request] > Controller > Service Layer > Repository Pattern > PostgreSQL

### Layer Dictionary:
1. **Controllers:** Only responsible for receiving HTTP requests, validating basic input schemas, and returning uniform JSON responses.
2. **Services Layer:** The brain of all financial business logic. Balance calculations, budget limit checks, and mutation verifications happen here. This layer does not care how data is stored or retrieved.
3. **Repository Pattern:** The database isolation layer. Write all complex SQL queries or Eloquent ORM calls here. If you optimize or change the database later, you only modify the Repository files without breaking the business logic.

## 3. Database Schema Design (ERD)

To support high scalability, use UUID or ULID for all Primary Keys instead of auto increment integers. This prevents ID collisions if you merge data or move to a distributed system later.

### Soft Deletes:
All tables implement soft deletes (`deleted_at`) to preserve audit trails, allow record restoration, and prevent historical calculation breaks. Soft deleting a user suspends the account without purging transaction history; soft deleting a wallet or category hides it from the UI while past transactions keep their binding intact; soft deleting a transaction acts as a cancellation/void mechanism; soft deleting a transfer triggers a balance reversal for both wallets involved.

### The Golden Rule of Money:
Never use FLOAT or DOUBLE data types to store money. Use BIGINT and store the value in its smallest unit (like cents for USD or no decimals for IDR) to avoid floating point calculation errors.

### Core Tables:

#### 1. Table: users
Stores the main user accounts.
* id (UUID, Primary Key)
* name (VARCHAR, Max 255)
* email (VARCHAR, Unique, Indexed)
* password (VARCHAR)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 2. Table: wallets
Stores funding sources or bank accounts.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed)
* name (VARCHAR) (Example: Cash Wallet, Chase Bank)
* balance (BIGINT, Default: 0) (Acts as a cache for the latest balance to avoid heavy aggregation queries)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 3. Table: categories
Groups income and expense types.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Nullable) (NULL means it is a global system default category)
* name (VARCHAR)
* type (ENUM: income, expense)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 4. Table: transactions
The main table recording money flowing in and out.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed)
* wallet_id (UUID, Foreign Key to wallets, Indexed)
* category_id (UUID, Foreign Key to categories, Indexed)
* amount (BIGINT) (Absolute transaction value)
* type (ENUM: income, expense)
* transaction_date (DATE, Indexed)
* notes (TEXT, Nullable)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 5. Table: transfers
Records money moving between internal wallets without messing up pure income or expense calculations.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed)
* from_wallet_id (UUID, Foreign Key to wallets)
* to_wallet_id (UUID, Foreign Key to wallets)
* amount (BIGINT)
* transfer_date (DATE, Indexed)
* notes (TEXT, Nullable)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

## 4. Scalability Strategy (High Traffic and Large Data)

The basic system will choke when transactions hit millions of rows. Implement these mitigations:

### Database Scalability
* **Database Replicas:** Split your database instances. Use one Primary for write operations (INSERT, UPDATE) and several Replicas for read operations (loading dashboards and histories).
* **Table Partitioning:** Partition the transactions table by month or year. PostgreSQL will only scan the relevant partition during a search instead of reading a massive table.

### Stateless Application and Horizontal Scaling
* Make the Laravel application servers completely stateless. Move all user session data to a global Redis cluster.
* Nginx acts as a Load Balancer distributing traffic evenly across multiple application containers. You can easily add or remove containers as needed.

### Accurate Balance Caching
Running a SUM function on millions of rows every time a user opens the app is a terrible idea.
* Update the latest balance in the wallets table and cache it directly in Redis.
* Use Database Transactions (Begin, Commit, Rollback) when a new transaction comes in. This ensures the PostgreSQL insert and Redis cache update happen simultaneously. If one fails, the whole process rolls back to maintain data consistency.

### Asynchronous Processing
Do not hold up the main API response for monthly report generation, PDF exports, or email notifications. Throw these heavy tasks into a Redis Queue and let a separate worker container process them in the background.

## 5. System Security and Operations

Financial apps are prime targets for cyber attacks. Layered security is mandatory.

* **Authentication:** Use strict expiration times for JWT or Laravel Sanctum tokens. Apply aggressive Rate Limiting on crucial endpoints to stop brute force attacks or transaction spamming.
* **Server Security:** Close unused ports on Ubuntu. Enforce passwordless SSH key logins. Configure Fail2Ban to automatically block suspicious IPs scanning your server.
* **Automated Backups:** Run automated scripts every night to dump the PostgreSQL database. Encrypt the backup files and push them to external cloud storage for disaster recovery.
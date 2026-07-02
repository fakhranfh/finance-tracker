# Database Schema Design with Soft Deletes (ERD)

To support high scalability and maintain audit trails, all Primary Keys use UUID or ULID instead of auto-increment integers. Soft deletes (`deleted_at`) are implemented across all tables to allow record restoration, error correction, and to prevent historical calculation breaks.

### Core Tables:

#### 1. Table: users
Stores the main user accounts. Activating soft delete on a user suspends the account without physically purging transaction history for auditing compliance.
* id (UUID, Primary Key)
* name (VARCHAR, Max 255)
* email (VARCHAR, Unique, Indexed)
* password (VARCHAR)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 2. Table: wallets
Stores funding sources or bank accounts. Soft deleting a wallet hides it from the UI while preserving past transaction history to keep historical financial reporting intact.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed)
* name (VARCHAR) (Example: Cash Wallet, Chase Bank)
* balance (BIGINT, Default: 0) (Acts as a cache for the latest balance to avoid heavy aggregation queries)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 3. Table: categories
Groups income and expense types. Soft deleting a category prevents new transactions from using it, but old transactions retain their category binding to avoid broken dashboard charts.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed, Nullable) (NULL means it is a global system default category)
* name (VARCHAR)
* type (ENUM: income, expense)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)

#### 4. Table: transactions
The main table recording money flowing in and out. Soft delete here acts as a transaction cancellation/void mechanism.
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
Records money moving between internal wallets. Soft deleting a transfer triggers a balance reversal for both the source and destination wallets.
* id (UUID, Primary Key)
* user_id (UUID, Foreign Key to users, Indexed)
* from_wallet_id (UUID, Foreign Key to wallets)
* to_wallet_id (UUID, Foreign Key to wallets)
* amount (BIGINT)
* transfer_date (DATE, Indexed)
* notes (TEXT, Nullable)
* created_at / updated_at (TIMESTAMP)
* deleted_at (TIMESTAMP, Nullable)
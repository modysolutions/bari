# 🌿 WordPress Stack

A high-performance, developer-friendly Docker stack designed for local WordPress development. It uses **PHP-FPM Alpine**, **Nginx**, **MariaDB**, and **Redis**, with automated SSL and project management via custom shell scripts.

## 🚀 Quick Start

### 1. Prerequisites
* **Docker & Docker Compose**
* **mkcert**: Install via `brew install mkcert` (Mac) or `sudo apt install mkcert` (Ubuntu). Run `mkcert -install` once.
* **Local DNS**: Map your `HOST_NAME` to `127.0.0.1` in your `/etc/hosts` file.
* **Satispress Key**: Create one on https://plugins.tamarindintelligence.com.

### 2. Permissions & Environment Setup
Before running any scripts, you must grant execution permissions to the automation tools:

```bash
# Grant execution permissions
sudo chmod +x ./bin/*

# Initialize environment file
cp sample.env .env
```
Modify all the variables in .env to match your local setup, especially HOST_NAME and SATISPRESS_KEY

### 3. Automated Installation
Run the master installation script from the project root:

```bash
./bin/install
```

**What this command does automatically:**
1.  **SSL Generation**: Runs `./bin/certs` to create locally trusted certificates.
2.  **Directory creation**: Creates the `logs` directory for `wp` and `xdebug`.
3.  **Container Launch**: Performs `docker compose up -d --build --force-recreate`.
4.  **Updates WordPress**: Updates WordPress core files to the version specified in your `.env` file while preserving the `web`.
5.  **Satispress Config**: Configures Composer to authenticate with the internal plugin repository using your `SATISPRESS_KEY`.
6.  **Install dependencies**: `./bin/composer install`.
7.  **Install WordPress**: If not already installed, it will run `wp multisite-install` with the credentials from your `.env` file.
8.  **Create Sites**: If the `SITES` variable is defined, it will create those sites in the multisite network and activate the Theme in all of them.
9.  **Activate Plugins**: If the `PLUGINS` variable is defined, it will install and activate those plugins in all sites of the multisite network.
---

## 🛠 Shell Script Toolbox (`./bin/`)

The `bin` directory contains proxy and automation scripts that handle complex tasks without requiring you to enter the containers.

| Command | Description | Example Usage |
| :--- | :--- | :--- |
| **`./bin/install`** | Performs a full stack deployment, including Git cloning and DB pulling. | `./bin/install` |
| **`./bin/config`** | Safely adds or updates a variable in your `.env` file. | `./bin/config SITE_NAME mysite` |
| **`./bin/db`** | Imports local SQL or pulls a fresh database dump from remote servers. | `./bin/db pull ei` |
| **`./bin/composer`** | Runs PHP Composer inside the app container. | `./bin/composer install` |
| **`./bin/wp`** | Wrapper for WP-CLI using aliases defined in `wp-cli.yml`. | `./bin/wp @ei plugin list` |
| **`./bin/certs`** | Generates SSL certificates for all local development domains. | `./bin/certs` |
| **`./bin/version`**| Updates or downgrades WordPress core files while preserving `wp-content`. | `./bin/version 6.2.2` |
| **`./bin/log`** | Standardized colored logging utility for scripts. | `./bin/log INFO "Message"` |

---

## 📁 Directory Structure
```text
.
├── app/                # WordPress Source Code (Managed by git/scripts)
│   ├── wp              # Core files (Managed by git, updated via ./bin/version)
│       ├── wp-admin    # Core admin files
│       ├── wp-includes # Core includes
│   ├── web             # Public directory (wp-content, uploads, custom plugins/themes)
│       ├── plugins     # Plugins go here
│       ├── themes      # Themes go here
│   ├── index.php       # Entry point for Nginx
│   ├── wp-config.php    # Custom configuration file
├── bin/                # Master Automation Scripts (Must be +x)
├── config/
│   ├── php/            # Custom .ini files (uploads, mail, redis)
│   ├── nginx/          # Nginx templates and config
│   ├── ssl/            # Generated SSL certificates
│   └── Dockerfile      # Custom PHP-FPM Image
├── .env                # Project Configuration
└── compose.yml         # Docker Orchestration
```

## 🐧 Permissions Note (Linux Users)
If you encounter permission issues while editing files in the `app/` directory, sync your local user with the Docker user:
```bash
sudo chown -R $USER:$USER app/
chmod -R 775 app/
```
The stack is pre-configured to synchronize UID 1000 to ensure seamless file sharing between your host and the containers.

### 🛠️ Available Commands

Use the project's binary wrapper to execute commands. This ensures the correct container and user (`www-data`) are used.

### 1. Create a New Migration
Generates a boilerplate migration file with a timestamped version and snake_case name.
```bash
./bin/wp tamarind migrate create "create search index table"
```
* **Output:** Creates `v20260330_001_create_search_index_table.php`
* **Template:** Includes `up()` and `down()` methods with `$wpdb` access.

### 2. Run Pending Migrations
Applies all migrations that haven't been recorded in the migration log table.
```bash
./bin/wp tamarind migrate up
```

### 3. Rollback Last Migration
Reverts the very last migration applied to the database.
```bash
./bin/wp tamarind migrate down
```

### 📝 Base Schema Example

Let's add a new table with an id and a post_id field in the up command:

```php
public function up() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tamarind_search_index';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id), 
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql); // Standard WP way to update schema
}
```

Now we can remove that table in the down command like this:
```php
public function down() {
    global $wpdb;
    $table = $wpdb->prefix . $this->table_name;
    $wpdb->query("DROP TABLE IF EXISTS $table");
}
```

### ⚠️ Best Practices

1.  **Wrapper Usage:** Always use `./bin/wp` instead of `docker exec` to maintain consistent file ownership.
2.  **Immutability:** Never edit a migration file after it has been executed in production. Create a new one for any further changes.
3. **Format:** Always add an up and a down command to a migration so database can be reverted to the original state before the migration was done.
4.  **Clean up:** If your migration adds significant data, remember to flush the **Redis** cache:
    ```bash
    ./bin/wp cache flush
    ```

*Documentation for Tamarind Media, S.L. Full Stack Development Team.*
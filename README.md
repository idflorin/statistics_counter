# Statistics Counter

Adds **weekly**, **monthly**, and **yearly** view counters for nodes on top of Drupal Core's `statistics` module.

✅ Compatible with **Drupal 11**  
✅ Uses Dependency Injection (DI) best practices  
✅ Integrates with **Views** for reporting and site building

---

## 🏗️ Requirements

- Drupal Core `statistics` module must be enabled.
- PHP 8.2+ (recommended PHP 8.3).

---

## 📦 Installation

1. Place the module in your project’s `modules/custom/` folder:
modules/custom/statistics_counter/
2. Enable the module:
drush en statistics_counter
Or enable it through the Drupal Admin UI.

3. Run database updates (if required):
drush updb

---

## 📊 Features

- Tracks:
- **Views this week**
- **Views this month**
- **Views this year**
- Automatically resets counters weekly, monthly, and yearly using Drupal **cron**.
- Integrates with **Views**:
- Adds fields:
 - "Views this week"
 - "Views this month"
 - "Views this year"
- Includes a relationship to join the `node_counter` table.

---

## 🖇️ How to Use in Views

1. Create a new View of **Content**.
2. Add a **Relationship** → “Node counter”.
3. Add Fields:
- “Views this week”
- “Views this month”
- “Views this year”

---

## ⚡ How It Works

1.  **Subscribes to `KernelEvents::TERMINATE`:** This ensures the view counting logic runs at the end of every page request.

2.  **Checks for Main Request:** It verifies that the current request is the main page request and not a sub-request (e.g., for AJAX or embedded elements). This prevents overcounting.

3.  **Retrieves Node Information:** It attempts to extract the viewed node object from the route parameters. It handles various scenarios where the node might be a `NodeInterface` object, another entity type with a `getEntityId()` method, an array containing the node ID (`nid`), or a direct numeric or string node ID.

4.  **Checks Configuration:** It checks if the "Count content views" setting is enabled in Drupal's statistics configuration (`statistics.settings`).

5.  **Verifies HTML Response:** It ensures that the response being sent to the client is an HTML page. This typically means a full node view.

6.  **Updates View Counts:** If all the above conditions are met, the subscriber updates the `node_counter` database table for the viewed node:
    * It attempts to insert a new record if one doesn't exist.
    * If a record already exists, it increments the `weekcount`, `monthcount`, and `yearcount` columns. It also updates the `timestamp` of the last view.

---

## 🛠️ Development Notes

- Dependency Injection used for:
- Database connection
- Request stack
- Route matching
- Config factory
- Module handler
- No deprecated APIs.
- Fully Drupal 11-safe.

---

## 👷‍♂️ Credits

Original module maintained at:  
[https://git.drupalcode.org/project/statistics_counter](https://git.drupalcode.org/project/statistics_counter)

This version updated for **Drupal 11** by 🧩 Drupal 11 Mastermind & ...

---

## 🆘 Support

For bug reports, please open an issue in your project's issue tracker or contact your development team.

# Statistics Counter

Adds **weekly**, **monthly**, and **yearly** view counters for nodes on top of Drupal Core's `statistics` module.

✅ Compatible with **Drupal 11**  
✅ Uses Dependency Injection (DI) best practices  
✅ Integrates with **Views** for reporting and site building

---

## 🏗️ Requirements

- Drupal Core `statistics` module must be enabled.
- PHP 8.1+ (recommended PHP 8.3).

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

- Counts are updated when a node is viewed (`entity.node.canonical` route).
- Uses the Symfony **`kernel.terminate`** event for performance-friendly updates.
- Resets counters via **`hook_cron()`** once per week/month/year as needed.

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

This version updated for **Drupal 11** by 🧩 Drupal 11 Mastermind.

---

## 🆘 Support

For bug reports, please open an issue in your project's issue tracker or contact your development team.

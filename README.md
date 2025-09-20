# Minerva

**Minerva** is a lightweight Markdown-based documentation and publishing system built in PHP. It supports browsing, editing, and rendering `.md` files, and is designed with flexibility, simplicity, and self-hosting in mind.

---

## 🚀 Features

- Clean and responsive Bootstrap 5 layout
- Parses Markdown using [Parsedown](https://parsedown.org)
- Directory-based content structure
- Admin editing via `edit.php`
- Navigation with breadcrumb and file tree
- Optional support for:
  - Previous/next navigation between files
  - Private/public book access
  - Git-based content versioning
- Ideal for publishing documentation, teaching materials, or personal notes

---

## 📁 Project Structure

```plaintext
/
├── content/                # Markdown content files (separate repo or submodule)
├── index.php              # Frontend viewer
├── edit.php               # Admin interface for editing Markdown
├── users.json             # Optional user system (for auth)
├── Parsedown.php          # Markdown parser
├── .gitignore
└── README.md
```

---

## 🛠 Setup Instructions

### 1. Clone the main app

```bash
git clone https://github.com/kylevanderburg/minerva.git
cd minerva
```

### 2. Add the content repository (two options)

#### Option A: As a Git submodule (recommended)

```bash
git submodule add https://github.com/yourusername/minerva-content.git content
git submodule update --init --recursive
```

To update later:

```bash
cd content
git pull origin main
```

#### Option B: Manual clone

```bash
git clone https://github.com/yourusername/minerva-content.git content
```

And make sure `/content` is listed in `.gitignore`.

---

## 🔐 Authentication (Optional)

If `config-users.json` exists, Minerva will prompt for login on edit pages.

Example format:

```json
{
  "yourusername": {
    "password": "$2y$10$hashedpasswordhere"
  }
}
```

Generate hashes with PHP:

```php
echo password_hash('yourpassword', PASSWORD_DEFAULT);
```

---

## ⚙️ Configuration Notes

- Content is loaded from the `/content` directory.
- Markdown files are rendered using Parsedown.
- Files are sorted naturally for chapter-like order.
- Directory names act like "books".

---

## 🔄 Development Ideas

- Support for "next" and "previous" buttons across directories
- Move/delete files from the admin interface
- Toggle visibility of books (public/private)
- Tagging and metadata for markdown files
- Search/indexing

---

## 📄 License

MIT License.

---

## Third-Party Libraries

This project includes [Parsedown](https://parsedown.org), a Markdown parser for PHP released under the MIT License.

---

## 👋 Credits

Minerva was created by [Kyle Vanderburg](https://kylevanderburg.com) and inspired by lightweight publishing systems like WriteBook.

Markdown parsing via [Parsedown](https://parsedown.org).
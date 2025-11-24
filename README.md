# 🌐 Uniform - The Self-Hostable Form Platform

Uniform is a **lightweight, self-hostable form backend and builder**.  
It provides **form endpoints** for collecting submissions and a **no-code form builder** for easy form creation.  

🚀 Use it as a **backend for contact forms**, surveys, or as a **Google Forms alternative**—**while keeping control over your data**.  

---

## ✨ Features  

✅ **Form Endpoints** – Collect form submissions via simple HTTP requests.  
✅ **No-Code Form Builder** – Drag-and-drop UI to create & share forms easily.  
✅ **Notification System** – Get submission alerts via email, webhooks, or Slack.  
✅ **Self-Hostable** – Install & run on your own server in minutes.  
✅ **SQLite Support** – Lightweight, fast, and easy to set up.  
✅ **Tailwind CSS** – Clean, modern, and mobile-friendly UI.  
✅ **Spam Protection** – Optional reCAPTCHA and honeypot fields.  
✅ **Built with Symfony 7.2** – Secure, extensible, and robust.  

---

## 🛠️ Installation (Self-Hosting)  

### **1️⃣ Install Dependencies**  
Make sure you have **PHP 8.2+**, **Composer**, and **SQLite** installed.  

```sh
git clone https://github.com/adeys/uniform.git
cd uniform
composer install
npm install
```

### **2️⃣ Configure Environment**
Create a `.env.local` file and update the database URL:

```ini
DATABASE_URL="sqlite:///%kernel.project_dir%/var/storage/database/uniform.sqlite"
APP_ENV=dev
APP_SECRET=your_secret_key
```

### **3️⃣ Run Database Migrations**
```sh
php bin/console doctrine:migrations:migrate
```

### **4️⃣ Build Frontend Assets**
The frontend assets are built with Tailwind CSS and Hotwire Stimulus using the `symfonycasts/tailwind-bundle`. Both the bundle binary and the npm Tailwind CLI operate on the same package; only the binary used changes. To build the styles run:

```bash
php bin/console tailwind:build
```

To enable automatic rebuild on file changes run:

```bash
php bin/console tailwind:build --watch
```

To use the npm Tailwind CLI instead, uncomment the `when@dev` section in `symfonycasts_tailwind.yaml` and add a `package.json` in the project root with the Tailwind CLI dev dependency, for example:

```json
{
  "devDependencies": {
    "@tailwindcss/cli": "^4.0.14",
    "tailwindcss": "^4.0.14"
  }
}
```

### **5️⃣ Start the Server**
```sh
symfony serve
```
Or with PHP’s built-in server:
```sh
php -S localhost:8000 -t public
```

Your instance should now be running at **http://127.0.0.1:8000** 🚀

---

## 🚀 Quick Start Guide

### **1️⃣ Using Uniform as a Form Endpoint**
**Submit data to your endpoint:**
```html
<form action="https://your-uniform-instance.com/e/{form_id}" method="POST">
  <input type="text" name="name" placeholder="Your Name" required />
  <input type="email" name="email" placeholder="Your Email" required />
  <button type="submit">Send</button>
</form>
```
📩 **Notifications**: Get email when a submission is received!

🚧 **Coming Soon** - Webhook and Slack integration setup.

### **2️⃣ Using the No-Code Form Builder**

🚧 **Coming Soon**

1. Log into your Uniform instance.
2. Create a new form using the drag-and-drop builder.
3. Share the form link to start collecting submissions.

---

## 🛡️ Security & Spam Protection

🚧 **Coming Soon**

✅ **API Authentication** – Protect endpoints with API keys or JWT tokens.  
✅ **Rate Limiting** – Prevent abuse by setting limits on requests.  
✅ **reCAPTCHA & Honeypot Fields** – Reduce spam submissions.

---

## 📖 API Documentation

🚧 **Coming Soon** – API docs will be available soon for developers who want to integrate deeper.

---

## 🤝 Contributing

We welcome contributions! 🎉

**To contribute:**
1. Fork the repo
2. Create a new branch (`git checkout -b feature-xyz`)
3. Commit changes (`git commit -m "feat: add feature XYZ"`)
4. Push and open a PR

Check out the [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## 🛠️ Tech Stack

- **Backend:** Symfony 7 (PHP 8.2)
- **Database:** Basically every database supported by Doctrine ORM (PostreSQL / MySQL / SQLite). Currently tested with SQLite.
- **Frontend:** Tailwind CSS + Hotwire Stimulus
- **Auth:** JWT-based authentication

---

## 📜 License

Uniform is **open source** and licensed under the **MIT License**.

---

## ⭐ Support & Community

💬 Join the discussion in [GitHub Issues](https://github.com/adeys/uniform/issues)  

🚀 Happy form building!

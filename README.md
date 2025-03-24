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
git clone https://github.com/raphalogou/uniform.git
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
<form action="https://your-uniform-instance.com/api/forms/{form_id}/submit" method="POST">
  <input type="text" name="name" placeholder="Your Name" required />
  <input type="email" name="email" placeholder="Your Email" required />
  <button type="submit">Send</button>
</form>
```
📩 **Notifications**: Get email, webhook, or Slack alerts when a submission is received!

### **2️⃣ Using the No-Code Form Builder**
1. Log into your Uniform instance.
2. Create a new form using the drag-and-drop builder.
3. Share the form link to start collecting submissions.

---

## 🛡️ Security & Spam Protection

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
3. Commit changes (`git commit -m "Add feature XYZ"`)
4. Push and open a PR

Check out the [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## 🛠️ Tech Stack

- **Backend:** Symfony 7.2 (PHP 8.2)
- **Database:** SQLite (Doctrine ORM)
- **Frontend:** Tailwind CSS + Hotwire Stimulus
- **Auth:** JWT-based authentication
- **Storage:** JSON-based submissions

---

## 📜 License

Uniform is **open source** and licensed under the **MIT License**.

---

## ⭐ Support & Community

💬 Join the discussion in [GitHub Issues](https://github.com/yourusername/uniform/issues)  
📢 Follow updates on **Twitter/X** (@uniform_forms)

🚀 Happy form building!

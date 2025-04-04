# TransAirEvent API



## 📌 Table of Contents
- [Features](#-features)
- [Technologies](#-technologies)
- [Setup](#-setup)
- [API Endpoints](#-api-endpoints)
- [Postman Testing](#-postman-testing)
- [Video Demo](#-video-demo)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features
- 🧑‍💼 User management (Passengers/Admins/Drivers)
- 🚗 Transfer scheduling system
- 🚙 Vehicle maintenance tracking
- 🔐 JWT Authentication
- 📦 RESTful JSON API

## 💻 Technologies
| Component       | Technology    |
|-----------------|---------------|
| Backend         | PHP 8.1+      |
| Database        | MySQL 5.7+    |
| Web Server      | xampp  |
| API Testing     | Postman       |

## 🛠️ Setup

### Prerequisites
- PHP 8.1+
- MySQL 5.7+
- Composer (for dependencies)

### Installation
```bash
# Clone repository
git clone https://github.com/abderrazzaqzouhair/TransAirEventAPi
cd TransAirEvent

# Configure environment
cp .env.example .env
nano .env  # Update database credentials

# Import database schema
mysql -u root -p transair_db < database/schema.sql

# Set permissions
chmod -R 755 storage
```

## 🌐 API Endpoints



### Passengers
| Method | Endpoint              | Description       | Auth |
|--------|-----------------------|-------------------|------|
| POST   | /utilisateurs         | Create passenger  | ❌   |
| GET    | /utilisateurs         | List passengers   | ❌   |
| GET    | /utilisateurs/{id}    | Get passenger     | ❌   |

### Transfers
| Method | Endpoint                       | Description            | Auth |
|--------|--------------------------------|------------------------|------|
| POST   | /transferts                    | Create transfer        | ✅   |
| PUT    | /transferts/{id}/assign        | Assign driver/vehicle  | ✅   |



📚 View complete API documentation



## 📹 Video Demo

[![Watch the demo](https://i.ytimg.com/vi/nTSJxQL297E/hqdefault.jpg?sqp=-oaymwEmCKgBEF5IWvKriqkDGQgBFQAAiEIYAdgBAeIBCggYEAIYBjgBQAE=&rs=AOn4CLB_iPZDy_LUfku7FfGJWZPMgVoZ5A)](https://youtu.be/ELuMtUITofo)

## 🤝 Contributing

Fork the repository  
Create your feature branch:
```bash
git checkout -b feature/your-feature
```
Commit your changes:
```bash
git commit -m 'Add some feature'
```
Push to the branch:
```bash
git push origin feature/your-feature
```
Open a pull request

## 📜 License
MIT License - See LICENSE for details.

---

### Companion Files Needed:

1. **`postman/collection.json`**
```bash
mkdir -p postman
# Export your Postman collection to this file
```

2. **`API_DOCS.md`**
```markdown
# API Documentation

## Authentication

### Login
```http
POST /utilisateurs_systeme/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "mot_de_passe": "password"
}
```

## Passengers

### Create Passenger
```http
POST /utilisateurs
Content-Type: application/json

{
  "nom": "Dupont",
  "prenom": "Jean",
  "statut": "VIP"
}
```
```

3. **`.github/CONTRIBUTING.md`** (optional)
```markdown
# Contribution Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Document all endpoints
- Keep commits atomic
```

---

This README includes:  
✅ Complete endpoint documentation  
✅ Postman testing instructions  
✅ Video demo integration  
✅ Setup guide  
✅ Contribution workflow  
✅ License information  

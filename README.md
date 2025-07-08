# 🌱 AgroVision

AgroVision is a Smart Agriculture System designed to help farmers monitor, manage, and improve their agricultural processes using modern IoT and software technologies.

## 🚀 Features

- 📊 Real-time data visualization (temperature, humidity, soil moisture)
- 🛰️ Sensor integration with IoT devices
- 🧠 Intelligent alerts and automation
- 🧾 Dashboard for admin and farmers
- 📱 Mobile-ready user interface
- 🧪 Smart decision support system
- 🌾 Crop management & scheduling

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript / React
- **Backend:** Laravel / PHP
- **Database:** MySQL
- **IoT:** Arduino / ESP32 + Sensors
- **Hosting:** Firebase / VPS / Cloud

## 📁 Project Structure

```bash
AgroVision/
│
├── storage/firebase/service_account.json  # 🔒 (Ignored in production)
├── public/                                # Assets and public-facing files
├── resources/                             # Views & frontend templates
├── routes/                                # Web/API routes
├── app/                                   # Laravel core logic
├── database/                              # Migrations and seeders
├── .env                                   # Environment config (ignored)
├── .gitignore                             # Git ignored files
└── README.md                              # Project documentation
```

## 🧑‍🌾 Target Users

- Farmers with smart devices
- Agriculture engineers
- Researchers
- Admins managing multiple farms

## 🔐 Security

- Sensitive credentials (e.g. Firebase) are **excluded** from Git
- Follows GitHub secret scanning rules

## 📦 Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/SalemAshraf/AgroVision.git
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install && npm run dev
   ```

3. Setup `.env` and database:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Migrate the database:
   ```bash
   php artisan migrate
   ```

5. Run the server:
   ```bash
   php artisan serve
   ```

## 📸 Screenshots

<img width="2625" alt="Untitled (3500 x 3500 px) (2)" src="https://github.com/user-attachments/assets/c14295b3-99fb-4c52-a2a1-848001926b98" />
<img width="2625" alt="Untitled (3500 x 3500 px) (3)" src="https://github.com/user-attachments/assets/a4ec5e26-b6ad-4586-9c06-5f6b823a5b07" />
<img width="2625" alt="Untitled (3500 x 3500 px) (4)" src="https://github.com/user-attachments/assets/7c604c08-e459-4fa5-91c2-914c0c0e75d0" />



## 👨‍💻 Author

**Salem Ashraf**  
[LinkedIn]([https://linkedin.com/in/salem-ashraf](https://www.linkedin.com/in/salem-ashraf-khudair-9a39a1219/)) 


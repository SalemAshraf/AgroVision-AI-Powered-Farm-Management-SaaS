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

![Untitled (3500 x 3500 px) (3)](https://github.com/user-attachments/assets/dc5c4975-8126-4b7e-9a32-d3fafeadacd5)
![Untitled (3500 x 3500 px) (2)](https://github.com/user-attachments/assets/3d562d7f-7457-4b97-9f92-44196a97c036)
![Untitled (3500 x 3500 px) (4)](https://github.com/user-attachments/assets/b2889b7a-9228-4edf-9df8-2c68da159376)
![Untitled (3500 x 3500 px) (1)](https://github.com/user-attachments/assets/a521f0d0-5612-4c5d-9330-02eed85a7dde)


## 👨‍💻 Author

**Salem Ashraf**  
[LinkedIn]([https://linkedin.com/in/salem-ashraf](https://www.linkedin.com/in/salem-ashraf-khudair-9a39a1219/)) 


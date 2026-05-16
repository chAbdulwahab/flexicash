# FlexiCash - Earning & Investment Platform

FlexiCash is a modern, responsive earning and investment platform built with PHP and MySQL. It allows users to invest in various plans, track their daily earnings, and participate in a referral program to maximize their profits. The platform also includes a robust admin panel for managing users, investments, and financial transactions.

## 🚀 Features

### For Users
- **Secure Authentication**: User registration and login system with password recovery.
- **Investment Plans**: Multiple investment packages with clear daily earning percentages.
- **Profit Calculator**: Interactive tool to estimate daily and monthly profits based on investment plans.
- **Personal Dashboard**: Track total balance, active investments, total earnings, and withdrawal history.
- **Referral Program**: Earn a **10% commission** on investments made by referred users.
- **Withdrawal System**: Easy-to-use withdrawal request system.
- **Earning via Tasks**: Watch videos and complete tasks to earn additional rewards.
- **Blog Section**: Stay updated with the latest news and platform updates.

### For Admins
- **Comprehensive Dashboard**: Overview of total users, active investments, and pending requests.
- **User Management**: Edit, delete, and manage user accounts and balances.
- **Financial Controls**: Process and approve/reject deposit and withdrawal requests.
- **Content Management**: Add and manage blogs and earning videos.
- **Platform Analytics**: Monitor the overall health and activity of the platform.

## 🛠️ Tech Stack
- **Backend**: PHP (Object-Oriented & Procedural)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3 (Vanilla CSS with CSS Variables), JavaScript (ES6+)
- **Icons**: FontAwesome (or equivalent)
- **Animations**: CSS-based transitions and keyframes for a premium feel.

## 📦 Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/chAbdulwahab/flexicash.git
   ```

2. **Database Setup**
   - Open PHPMyAdmin.
   - Create a new database named `earnings_db`.
   - Import the provided SQL schema (usually named `database.sql` or similar, if available).

3. **Configuration**
   - Navigate to `includes/config.php`.
   - Update the database credentials to match your local environment:
     ```php
     $host = 'localhost';
     $dbname = 'earnings_db';
     $username = 'root';
     $password = ''; // Add your password if applicable
     ```

4. **Run the Application**
   - Place the project folder in your local server directory (e.g., `htdocs` for XAMPP).
   - Start Apache and MySQL services.
   - Access the site via `http://localhost/flexicash`.

## 🖥️ UI Design
The application features a premium dark/blue theme with:
- **Glassmorphism**: Subtle blur effects on cards and modals.
- **Micro-animations**: Hover effects and smooth transitions for interactive elements.
- **Responsive Layout**: Optimized for mobile, tablet, and desktop screens.
- **Dynamic Calculator**: Real-time profit estimation without page reloads.

## 🤝 Referral Program
FlexiCash encourages growth through its referral system. Users get a unique referral link that they can share. When a new user signs up and invests using that link, the referrer receives a **10% bonus** of the investment amount directly into their balance.

## 📄 License
This project is for educational and portfolio purposes. Please ensure you have the necessary rights before using it in a production environment.

---
Developed by [Abdul Wahab](https://github.com/chAbdulwahab)

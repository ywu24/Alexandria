# Alexandria
[![Live Demo](https://img.shields.io/badge/demo-live-green)](https://yihaowu.dev/Alexandria)
<div>
  <a href="https://github.com/ywu24">
    <img src="https://github.com/ywu24.png" width="40" height="40" style="border-radius:50%" alt="Yi Hao Wu"/>
  </a>
  &nbsp;
  <a href="https://github.com/Marcoding04">
    <img src="https://github.com/Marcoding04.png" width="40" height="40" style="border-radius:50%" alt="Marco Mezzanotte"/>
  </a>
  &nbsp;
  <a href="https://github.com/Pix36">
    <img src="https://github.com/Pix36.png" width="40" height="40" style="border-radius:50%" alt="Luca Pitti"/>
  </a>
  <br/>
  <sub>Built by <b>Yi Hao Wu</b> · <b>Marco Mezzanotte</b> · <b>Luca Pitti</b></sub>
</div>

## 📚 Digital Library Management System

Alexandria is a comprehensive web-based library management system designed to streamline the management of books, users, loans, and reservations. It provides intuitive features for both administrators and end-users with a modern interface.

## 🌐 Live Demo
A live demo of Alexandria is available here: [https://yihaowu.dev/Alexandria](https://yihaowu.dev/Alexandria)

> Demo credentials:
>  - `librarian@example.com` / `password123`
>  - `user@example.com` / `password123`


## 🔧 Requirements and Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer (for dependency management)

### Installation

1. **Clone the repository** and navigate to the project directory:
   ```bash
   git clone https://github.com/username/Alexandria.git
   cd Alexandria
   ```

2. **Install dependencies** using Composer:
   ```bash
   composer install
   composer dump-autoload
   ```

3. **Configure the database**:
   - Create a MySQL database and add its name to `.env` (e.g., `alexandria`).
   - Run SQL scripts to create tables.

4. **Configure the `.env` file**:
   Create a `.env` file in the root directory with the following variables (example provided):
   ```ini
   DB_HOST=localhost
   DB_USER=username
   DB_PASS=password
   DB_NAME=alexandria
   EMAIL_USERNAME=example@smtp-server.com   # email server username for sending system emails
   EMAIL_PASSWORD=password                  
   EMAIL_BIBLIO=biblio@example.com          # librarian's email to which the system emails (for the librarian) will be sent
   APP_DEBUG=true                           # set to true if you want debug messages to be print in the browser, false otherwise
   APP_URL=http://localhost/Alexandria      # base url of the website
   ```

5. **Start the web server** (e.g., Apache or Nginx) and access the application.

## 📂 Project Structure

- **`src/`**: Core application logic, including services, helpers, and views.
- **`dashboard/`**: Admin panel for managing books, users, and reports.
- **`edit_profile/`**: User account page (info, change password/profile picture, delete account).
- **`libro/`**: Book info page
- **`lista/`**: Books catalog page
- **`nav/`**: Navbar
- **`notifiche/`**: Notification page
- **`prenotazione/`**: User reservation page, Admin reservation management page.
- **`recensione/`**: Page for writing reviews
- **`segnalazione/`**: Page for writing reports
- **`auth/`**: Authentication management (login, registration, logout).
- **`api/`**: API endpoints for asynchronous operations.
- **`utils/`**: DB connection and Emailing helpers.
- **`js/`**: JavaScript files for client-side logic.
- **`css/`**: CSS stylesheets for the user interface.
- **`img/`**: Graphic resources (book covers, icons, etc.).

## 🛠️ Key Features

### For **End Users**
- **Book Catalog**: Browse books by genre, author, or title.
- **Loans and Reservations**: Manage your loans and reservations.
- **User Profile**: View and edit your account information.
- **Reviews**: Leave reviews for books you've read.

### For **Administrators**
- **Book Management**: Add, edit, or delete books and copies.
- **User Management**: Add, edit, or delete users.
- **Reports**: View and manage user reports.
- **Reservations and Loans**: View and manage book reservations and loans.
- **Statistics**: Access detailed statistics on books, users, and loans.

## 🔄 Contributing to the Project

To contribute to Alexandria, follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and commit them.
4. Open a Pull Request.

## 📜 License

This project is released under the **MIT** license.

## 📧 Contact

For questions or suggestions, contact us at:
- **Yi Hao Wu** — [wu.2106064@studenti.uniroma1.it](mailto:wu.2106064@studenti.uniroma1.it)
- **Marco Mezzanotte** — [mezzanotte.2150101@studenti.uniroma1.it](mailto:mezzanotte.2150101@studenti.uniroma1.it)
- **Luca Pitti** — [pitti.2127200@studenti.uniroma1.it](mailto:pitti.2127200@studenti.uniroma1.it)

---

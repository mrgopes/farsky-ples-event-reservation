# Farsky Ples Event Reservation

This is a simple reservation system for the purposes of the Parish Ball in Cierna Voda, Slovakia.
Under the hood it's a Laravel app with Vue.js frontend via Inertia.js.

## Requirements
- PHP 8.4
- Composer
- Node.js 22+
- NPM
- MySQL

## Installation
- Clone the repository
- Run `composer install` to install PHP dependencies.
- Run `npm install` to install JavaScript dependencies.
- Copy `.env.example` to `.env` and configure your database and other settings.
- Run `php artisan key:generate` to generate the application key.
- Run `php artisan migrate:fresh` to create the database schema.
- Run `npm run dev` to start the development server.
- Access the application at `http://localhost:8000`.

Optionally, you may want to create a new user via `php artisan make:admin <name>`

## Features
- Administration dashboard for logged-in users for event management
- Multi-user support with simple roles and permissions
- Email notifications for reservation confirmations/cancellations etc.
- Responsive design for mobile and desktop
- Comprehensive design features such as banner, logo, overline title and markdown description of events
- Customizable reservation limits and settings per event
- Support for custom SVG seat maps
- Payment pairing either manually or via an CSV file import
- Printable seat map
- QR Codes for check-in at the event

## Ideas for future improvements
- Better implementation of "plugins" or "modules" to allow easier extension of the system
- Mass mailing system to all participants of an event
- Integrating with a paywall/payment gateway for automatic payment processing

## Contributing
Contributions are welcome! Please fork the repository and create a pull request with your changes.
In case of issues feel free to open a pull request as well.

## License
This project is licensed under the GPLv3 License. See the LICENSE file for details.
The author is not responsible for any damages caused by the use of this software.

Developed by Michal Barnáš in 2025.

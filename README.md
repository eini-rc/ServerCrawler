# Laravel PHP Project

This project is built with Laravel, a popular PHP framework. Follow the instructions below to run the project on your local machine.

## Prerequisites

Make sure you have the following software installed on your machine:

- PHP (version 8.1.19)
- Composer (version 2.5.7)
- MongoDB 
- MongoDB PHP extension

## Installation

1. Clone the repository to your local machine:

   ```bash
   git clone https://github.com/eini-rc/ServerCrawler.git
   ```
2. Navigate to the project directory:
     ```bash
   cd ServerCrawler
   ```
3. Install project dependencies using Composer:
 
     ```bash
   composer update
   ```
   
4. Create a copy of the .env.example file and rename it to .env:

     ```bash
     cp .env.example .env
   ```
   
   and upade with your local such as:
   ```bash
   DB_CONNECTION=mongodb
   DB_DATABASE=CrawlerDB
   DB_HOST=localhost
   DB_PORT=27017
   ```
   
5. Run database migrations:
     ```bash
     php artisan migrate
      ```
   
 ## Running the Application
To start the Laravel development server, run the following command:

```bash
 php artisan serve
```
This will start the development server at http://localhost:8000.

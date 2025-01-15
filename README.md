## About System
The system is a simple laundry management system with multilanguage support.
- The database is a MySQL/MariaDB database.
- The frontend is a [MaryUI](https://mary-ui.com/) 1.41
- The backend is a [Laravel](https://laravel.com/) 11.9 with [Livewire](https://livewire.laravel.com/) 3.5

## Installation
1. Clone the repository
``` bash
    git clone git@github.com:jichiduo/cuci.git
```

2. Install the dependencies 
``` bash
    Composer install 

    npm run build
```
3. Configure the database connection, create a database name cuci
``` bash
    cp .env.example .env
```
4. Run the migrations 
``` bash
    php artisan migrate:fresh --seed
```    
5. Run the server 
``` bash
    php artisan server
```
## Login

- admin user: jichiduo@163.com 
- password: 12345

## Features
- User authentication
- Laundry order management
- Laundry item management
- Laundry service management
- Laundry pricing management
- Laundry transaction management
- Laundry customer management
- Print to local printer via https://github.com/imTigger/webapp-hardware-bridge

## Contact
For any questions or issues, please contact us at [jichiduo@163.com](mailto:jichiduo@163.com).
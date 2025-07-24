# Local Setup and Testing

### Requirements
* PHP, MySQL and Git

### Setup
Clone the repo
```bash
$ git clone https://github.com/oluwaCyna/FileUploadAndSharingAPI.git
cd file-upload-sharing-api
```

install dependencies
```bash
$ composer install
```

Then, create a `.env` file based on `.env.example`
```bash
$ cp .env.example .env
```

set up database
   - create a new database
   - fill the details in .env file

    DB_DATABASE=your_db_name
    DB_USERNAME=your_db_user
    DB_PASSWORD=your_db_password

set up mail account
   - use mailtrap.io for local mailing
   - set up your smtp account
   - get the smtp info and fill the .env file

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=your_mailtrap_username
    MAIL_PASSWORD=your_mailtrap_password
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=example@example.com
    MAIL_FROM_NAME="${APP_NAME}"

run migration
```bash
$ php artisan migrate
```

start the server
```bash
$ php artisan serve
```

start queue worker
```bash
$ php artisan queue:work --queue=emails
```

run scheduler
```bash
$ php artisan schedule:run
```

run the test
```bash
$ php artisan test
```
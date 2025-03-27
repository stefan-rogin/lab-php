# Blog App

The demo project is based on Laravel 12.3.0 with Vue [starter kit](https://laravel.com/docs/12.x/starter-kits#vue), from which all irrelevant files were removed, including the demo Vue app and most of the styling boilerplate. 

## Setup

### Prerequisites

In order to run the project, a working Laravel environment is needed.

- Laravel, PHP, Composer
- NodeJs and NPM
- MySQL

### Installation

- Create a MySQL database for the project and, optionally, create a user for the application.

```
mysql> create database blog;
mysql> create user 'blog-app'@'localhost' identified by '...';
mysql> grant all privileges on blog.* to 'blog-app'@'localhost';
```

- Unzip the project and create a local `.env` file from the template

```
$ unzip blog-vue-app.zip
$ cd blog-vue-app/
$ cp .env.example .env
```

- Configure `.env` file with the database connection params.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=blog-app
DB_PASSWORD=...
```

- Install and setup the project, then run the initial migration scripts to have the tables created.

```
$ composer install
$ php artisan key:generate
$ php artisan migrate
$ npm install && npm run build
```

- Start the project in dev mode.

```
$ composer run dev
```

- Open the project's home page in a browser window and interact with the UI to fetch the external blog posts, then filter through them with the filter input.

```
e.g. http://localhost:8000
```

## Overview

The application fetches and stores a list of blog entries from `https://api.vercel.app/blog`. The size of the dataset is minimal, therefore no special precautions were taken for their retrieval. The external fetching is performed on demand by a `POST /api/import` request to the project's API. 

Stored posts are retrievable with a `GET /api` request. Given the size of the dataset, the endpoint responds with all posts, without pagination.

A simple Vue component provides user interface for interacting with the API, showing either a *Fetch posts* button if the store is empty, or the list of posts if not empty. Filtering objects in the UI is performed client-side, which was considered more appropriate for the requirement to filter the list as the user types.

### Classes and components

- `App\Http\Controllers\PostController`: Controller class for the app's API
- `App\Http\Resources\PostResource`: Resource definition for posts
- `App\Models\Category`: Model class for Category 
- `App\Models\Post`: Model class for Post
- `App\Services\PostService`: Service for retrieval and persistence of posts, using a client service
- `App\Services\BlogClientService`: Web service client for fetching external posts for import
- `Database\Factories\CategoryFactory`: Factory class for Category
- `Database\Factories\PostFactory`: Factory class for Post
- `migrations/2025_03_25_091604_create_posts_table.php`: DB starter migration for posts and categories
- `Seeders\DatabaseSeeder`: Handy DB seeder
- `resources/js/pages/Welcome.vue`: The UI Vue component
- `routes/api.php`: API routes mapping

### Tests

To run the project tests, use `$ php artisan test`. Optionally, run test with `--coverage` suffix for coverage reporting (requires debugger).

Tests:
- Feature
    - `PostApiTest`: Tests for the API controller.
    - `BlogClientServiceTest`: Tests for the web client service.
    - `PostServiceTest`: Tests for the import service.
- Unit
    - `PostServiceValidatorTest`: Tests for the PostResource validation.

### Remarks

- Authentication was considered out of scope, given that the starter kit comes with a functional solution.
- Handling char encoding was considered out of scope, given the external service behavior.
- Pest expectations were preferred, when possible, but the tests use assertions too, generally for DB checks. Consistency within a test case was the most convenient compromise.
- The project logs to the default file `storage/logs/laravel.log`.
- More detailed explanations of the implementations are found in the code, as comments.

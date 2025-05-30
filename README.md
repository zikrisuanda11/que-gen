# Que-Gen: AI-Powered Question Bank System

## Overview

Que-Gen is a sophisticated educational tool designed to facilitate the creation and management of examination questions through artificial intelligence integration. This application empowers educators to efficiently generate both individual and batch questions, thereby optimizing the assessment preparation process.

## Core Features

- **Comprehensive Question Repository**: Systematically organize and maintain questions categorized by subject matter
- **AI Question Generation**: Create high-quality questions with minimal input through advanced AI algorithms
- **Batch Question Creation**: Generate multiple questions simultaneously to maximize efficiency
- **Advanced Search Capabilities**: Locate specific questions through robust filtering mechanisms
- **Responsive Design**: Access functionality across various devices and screen sizes

## Usage Instructions

### Individual Question Generation

1. Navigate to the subject page
2. Select the "Questions" tab
3. Click the "Generate Question with AI" button
4. Enter the category and specify the number of answer options
5. Submit the form to automatically create a new question

### Batch Question Generation

1. Navigate to the subject page
2. Select the "Questions" tab
3. Click the "Generate Bulk Questions" button
4. Specify the category, number of answer options, and total questions required
5. Submit the form to generate multiple questions simultaneously

## Technical Architecture

- Laravel Framework
- Filament Admin Panel
- MySQL Database
- AI Integration API

## API Documentation

### Single Question Generation Endpoint

```
URL: localhost:8000/generate-question
Method: POST
Request Body: 
{
    "subject_name": "Biology",
    "category": "Photosynthesis",
    "answer_option": 4
}
```

### Bulk Question Generation Endpoint

```
URL: localhost:8000/generate/bulk
Method: POST
Request Body:
{
    "subject_name": "Elementary Science Grade 2",
    "category": "Science",
    "answer_option": 3,
    "total_question": 10
}
```

## System Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL Database

## Installation Procedure

1. Clone the repository
   ```
   git clone https://github.com/organization/que-gen.git
   cd que-gen
   ```

2. Install dependencies
   ```
   composer install
   npm install
   ```

3. Configure environment
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Initialize database
   ```
   php artisan migrate
   php artisan db:seed
   ```

5. Launch application
   ```
   php artisan serve
   ```

6. Access the application at `http://localhost:8000`

## Access Control

The system implements role-based access control:
- **Teachers**: Authorized to create, modify, and remove questions, with access to AI generation features
- **Administrators**: Possess all teacher privileges plus user management capabilities

## Contribution Guidelines

We welcome contributions to enhance the system. Please fork the repository and submit pull requests for review.

## License

This software is distributed under the [MIT License](LICENSE).
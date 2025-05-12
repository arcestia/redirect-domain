# Domain Randomizer

A PHP application that provides domain randomization and redirection based on configurable rules. When accessed through specific domains, it generates random subdomains and redirects to configured target domains based on predefined rules.

## Features

- Domain-specific redirection rules
- Random subdomain generation
- RESTful API for managing domains and rules
- MariaDB integration for persistent storage
- Configurable through environment variables
- Comprehensive logging of all redirects

## Prerequisites

- PHP 8.1 or higher
- MariaDB 10.5 or higher
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/arcestia/domain-randomizer.git
cd domain-randomizer
```

2. Install dependencies:
```bash
composer install
```

3. Create and configure the `.env` file:
```bash
cp .env.example .env
# Edit .env with your database credentials and settings
```

4. Set up the database:
```bash
mysql -u root < schema.sql
```

## Configuration

Configure the application through the `.env` file:

```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=domainrandomizer
DB_PORT=3306

APP_ENV=development
APP_DEBUG=true
APP_PORT=8080
```

## Usage

1. Start the development server:
```bash
composer start
```

2. The application will handle incoming requests based on the hostname used to access it.

Example:
- Access through `domainA.com` → Redirects to random subdomain on either `TargetA.com` or `TargetB.com`
- Access through `domainB.com` → Redirects to random subdomain on either `TargetC.com` or `TargetD.com`

## API Documentation

See [API.md](API.md) for detailed API documentation.

Quick API examples:

1. List all source domains:
```bash
curl http://localhost:8080/api/sources
```

2. Add new target domain:
```bash
curl -X POST http://localhost:8080/api/targets \
  -H "Content-Type: application/json" \
  -d '{"domain": "example.com"}'
```

3. Create new rule:
```bash
curl -X POST http://localhost:8080/api/rules \
  -H "Content-Type: application/json" \
  -d '{
    "source_domain": "source.com",
    "target_domain": "target.com"
  }'
```

## Database Schema

The application uses four main tables:

1. `source_domains`: Stores domains that can access the service
2. `target_domains`: Stores potential redirect target domains
3. `domain_rules`: Maps relationships between source and target domains
4. `redirects`: Logs all redirections for tracking

## Development

1. Clone the repository
2. Install dependencies: `composer install`
3. Create `.env` file with your configuration
4. Set up the database using `schema.sql`
5. Run the application: `composer start`

## Security Considerations

1. Protect your `.env` file - never commit it to version control
2. Implement proper authentication for API endpoints in production
3. Use HTTPS for all production traffic
4. Regularly backup your database
5. Monitor redirect logs for abuse

## Author

Laurensius Jeffrey

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

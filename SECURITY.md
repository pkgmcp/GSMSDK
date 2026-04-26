# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | ✅ Active support  |
| < 1.0   | ❌ End of support  |

## Reporting a Vulnerability

If you discover a security vulnerability in GSMSDK, please follow these steps:

1. **Do NOT open a public GitHub issue**
2. Send an email to security@gsmsdk.io with the details
3. Include the following information:
   - Type of vulnerability
   - Full paths of source file(s) related to the vulnerability
   - Location of the affected source code
   - Special configuration required for reproduction
   - Step-by-step instructions to reproduce
   - Proof-of-concept or exploit code (if possible)
   - Impact of the vulnerability

Our security team will acknowledge your report within 48 hours and provide a resolution timeline.

## Vulnerability Response Timeline

- **Initial Response**: Within 48 hours
- **Assessment**: Within 7 days
- **Patch Release**: Within 30 days (critical), 90 days (moderate), 180 days (low)

## Security Best Practices

### For GSMSDK Developers

✅ Keep dependencies up to date  
✅ Use parameterized queries (SQL injection prevention)  
✅ Validate and sanitize all user input  
✅ Implement proper authentication and authorization  
✅ Use HTTPS in production  
✅ Keep secrets in environment variables (not in code)  
✅ Enable error reporting only in development  
✅ Implement rate limiting on API endpoints  
✅ Use CSRF protection for forms  
✅ Regular security audits

### For GSMSDK Users

✅ Always use the latest stable version  
✅ Never commit secrets to version control  
✅ Use environment-specific configurations  
✅ Enable HTTPS in production  
✅ Implement proper access controls  
✅ Regular security updates  
✅ Monitor application logs  
✅ Use strong passwords and authentication  
✅ Enable two-factor authentication where possible  
✅ Backup regularly

## Security Features in GSMSDK

### Built-in Protections

- **SQL Injection Prevention**: All database queries use parameterized statements
- **CSRF Protection**: Built-in CSRF token generation and validation
- **XSS Prevention**: Auto-escaping in views (configurable)
- **CORS Support**: Configurable CORS headers
- **Session Security**: Secure session management
- **Input Validation**: Request validation with customizable rules
- **Error Handling**: Production-safe error messages (no stack traces)
- **Type Safety**: PHP 8.5+ type system prevents many common bugs

### Secure Defaults

```php
// GSMSDK uses secure defaults
[
    'debug' => false,              // Disabled in production
    'session.secure' => true,      // HTTPS only cookies
    'session.http_only' => true,   // Prevent JavaScript access
    'cors.enabled' => false,       // Disabled by default
    'csrf.enabled' => true,        // Enabled by default
]
```

## Known Security Considerations

### Desktop Applications
- Electron apps inherit Electron's security model
- Enable `contextIsolation` and `nodeIntegration: false`
- Use `preload` scripts for IPC communication
- Validate all IPC messages

### Mobile Applications
- Android: Use Android Keystore for sensitive data
- iOS: Use Keychain for secure storage
- Never hardcode API keys in mobile apps
- Use certificate pinning for API communication

### ADB & Fastboot Operations
- ADB operations require device authorization
- Fastboot can permanently modify devices
- Always validate image files before flashing
- Use secure channels for remote operations

## Compliance

GSMSDK aims to comply with:
- **OWASP Top 10** - Web application security
- **PCI DSS** - Payment card industry standards (when applicable)
- **GDPR** - Data protection and privacy (EU)
- **CCPA** - Consumer privacy (California)

## Security Resources

- [OWASP Cheat Sheets](https://cheatsheetseries.owasp.org/)
- [PHP Security Consortium](https://phpsecurity.readthedocs.io/)
- [OWASP PHP Security Project](https://owasp.org/www-project-php-security/)

## Questions?

For security-related questions, contact: security@gsmsdk.io

---

*Last updated: 2026-04-26*

## Flash Tool Security Considerations

The web flash tool interfaces with device firmware at a low level. Follow these guidelines:

### For Production Deployment

⚠️ **WARNING**: Flash operations can brick devices. Only deploy in secure environments.

- **Isolate flash operations**: Run on internal networks only, not publicly accessible
- **Authentication**: Implement authentication middleware before flash routes
- **Authorization**: Restrict flash operations to authorized users only
- **HTTPS**: Always use HTTPS in production
- **Session security**: Use secure, HttpOnly cookies for session management
- **CSRF protection**: Ensure all flash operations require CSRF tokens

### Security Features Included

✅ CSRF token protection on all form submissions  
✅ Input validation on all parameters  
✅ Prepared statements for database operations  
✅ Output escaping in templates ({{ }} vs {!! !!})  
✅ Session-based authentication  
✅ Path validation for template inclusion  
✅ Error handling prevents information disclosure  

### Flash Operation Safety

⚠️ **CRITICAL**: Flash operations modify device firmware

- Always verify device connection before flashing
- Backup important data before flash operations
- Use test devices, not production devices
- Monitor flash operations actively
- Have recovery procedures ready
- Never flash untrusted firmware images

### Security Testing

Run security checks before deployment:

```bash
# Check for vulnerabilities
composer require --dev enlightn/security-checker
composer security:check

# Code style and analysis
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Static analysis (if configured)
# ./vendor/bin/phpstan analyse
```

### Common Vulnerabilities to Watch For

1. **Command Injection**: Never pass user input to shell commands
2. **Path Traversal**: Validate all file paths
3. **XSS**: Always escape output in templates
4. **CSRF**: Use tokens on all state-changing operations
5. **Session Fixation**: Regenerate session IDs after login
6. **SQL Injection**: Always use parameterized queries

## Contact

**Security Email**: security@gsmsdk.io  
**PGP Key**: [Will be published separately]

---

*Last updated: April 2026*

## Credits

Security improvements contributed by the GSMSDK community.

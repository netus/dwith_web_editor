# dwith_web_editor
Version v1.0

A lightweight web-based HTML editor built with PHP and CodeMirror, featuring live preview, text selection highlight synchronization, and autosave history.

## Features
- Live HTML preview using iframe srcdoc
- Text selection highlight sync between editor and preview
- Full-text search with highlight and auto-scroll
- Autosave every 60 seconds
- Manual save with history panel
- Version history (latest 20 autosaves)
- No database required
- Single-user, file-based storage

## Requirements
- PHP 8.0 or higher
- Web server with PHP support
- Writable autosave/ directory

## Directory Structure
```├── index.php
├── api.php
├── autosave/
│   └── .gitkeep
├── README.md
├── LICENSE
└── .gitignore
```

## Installation
1 Upload all files to a PHP-enabled server
2 Ensure autosave/ directory exists and is writable
   chmod 755 autosave
3 Open index.php in a browser

## Usage
- Edit HTML and CSS in the left editor pane
- Preview updates automatically in the right pane
- Select text in the editor to highlight it in the preview
- Use the search box to find and highlight text in the editor
- Click Save to manually store a version
- Autosave runs every 60 seconds
- Open History to restore previous versions

## API Endpoints
All endpoints are located in api.php.

POST ?action=save  
Body JSON:
{
  "ts": "YYYYMMDD_HHMMSS",
  "code": "<html>...</html>"
}

GET ?action=list  
Returns latest autosave metadata

GET ?action=load&file=FILENAME.json  
Loads a specific autosave

## Storage Rules
- Maximum single file size: 2 MB
- Maximum total autosave storage: 2 GB
- Maximum retained versions: 20
- Autosaves older than 48 hours are automatically cleaned

## Security Notice
This project does NOT include authentication or access control.
It is intended for trusted or local environments only.
Do NOT deploy on a public server without additional protection such as HTTP auth or IP restrictions.

## License
MIT License

## Author
Johnny@ideavat - https://dwith.com
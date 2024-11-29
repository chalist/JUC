# JSON User Creator Documentation

## Overview
JSON User Creator allows you to bulk create WordPress users from a JSON file. It supports mapping JSON fields to user attributes and handles Persian/Arabic text conversion.

## How to Use
1. Upload a JSON file containing user data
2. Map JSON fields to WordPress user fields (Username, Email, First Name, Last Name)
3. Preview the mapped data in real-time
4. Optionally enable post author updates
5. Click "Create Users" to process the import

## Features
- Automatic Persian/Arabic to English conversion for usernames
- Automatic email generation if not provided
- Real-time preview of converted data
- Post author update capability
- Duplicate username handling

## JSON Format
Your JSON file should be an array of objects containing user data. Example:

```json
[
    {
        "name": "John Doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "post_id": [12, 121, 332, 45]
    }
]
```

## Contributing
This plugin is intentionally kept simple and straightforward. However, there's always room for improvement! If you:
- Find any bugs or issues
- Have ideas for new features
- Want to improve the documentation
- See opportunities for code optimization

Please feel free to:
1. Open an issue to report bugs or suggest features
2. Submit a pull request with your improvements
3. Fork the project and build upon it

Your contributions, no matter how small, are greatly appreciated and will help make this plugin better for everyone!

If you need anything, feel free to contact me on telegram: [@chalist](https://t.me/chalist) or send email to: [chalist1@gmail.com](mailto:chalist1@gmail.com)

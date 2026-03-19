# Hostel PRO

**A comprehensive WordPress plugin for hostel and small hotel management with online booking capabilities.**

![Version](https://img.shields.io/badge/version-2.0.7-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)
![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)

## Overview

Hostel PRO is a complete reservation system and website solution for hostels, B&Bs, and small hotels. Built as a WordPress plugin, it provides everything you need to manage rooms, accept online bookings, track payments, and synchronize with external booking platforms.

This project is now **open source** and available for everyone under the GPL v2 license.

---

## Features

### Room Management
- **Unlimited rooms** - Create as many rooms as you need
- **Room types** - Private rooms or dormitories (male/female/mixed)
- **Bathroom options** - Ensuite or shared bathroom designation
- **Flexible pricing** - Set prices per bed or per room
- **Extra beds** - Configure additional beds with separate pricing
- **Child pricing** - Special rates for children's beds
- **Whole dorm pricing** - Offer entire dorm rooms at a discount

### Online Bookings & Payments
- **PayPal integration** - Accept payments via PayPal (IPN & PDT supported)
- **Stripe integration** - Modern payment processing with Stripe
- **Manual booking mode** - Accept bank transfers, checks, or payment on arrival
- **Multiple booking mode** - Allow multiple rooms in a single session/payment
- **Advance payment** - Configurable deposit percentage or fixed amount
- **Automatic cleanup** - Remove unpaid pending bookings after specified hours

### Booking Management
- **Searchable booking list** - Filter by guest name, email, room, status, or booking ID
- **Past & upcoming bookings** - Separate views for historical and future reservations
- **Payment tracking** - Mark bookings as paid, track amounts due
- **Booking statuses** - Active, pending, cancelled status management
- **Custom fields** - Add unlimited custom fields to booking forms
- **Email notifications** - Automated emails to guests and administrators

### Customizable Booking Form
- **Shortcode-based** - Easy embedding anywhere on your site
- **Visual editor support** - Rearrange fields in WordPress editor
- **Custom fields** - Text, textarea, dropdown, checkbox, file upload fields
- **Required fields** - Mark fields as mandatory
- **CSS customization** - Full control over form appearance
- **Responsive design** - Mobile-friendly booking experience

### Availability & Calendar
- **Set unavailable dates** - Block dates when property is closed
- **Room-specific unavailability** - Schedule maintenance without closing entire property
- **Room calendars** - Visual calendar showing availability for each room
- **Multiple calendar views** - Overview calendar for all rooms
- **Date picker** - jQuery UI datepicker with localization support
- **Min/max stay** - Set minimum and maximum stay requirements

### Discounts & Surcharges
- **Date-based pricing** - Different prices for specific date ranges
- **Weekday pricing** - Set different prices for days of the week
- **Length of stay discounts** - Discounts based on number of nights
- **Coupon codes** - Distribute discount codes to guests
- **Seasonal pricing** - Perfect for high/low season rate changes
- **Minimum price discounts** - Automatic discounts when booking total reaches threshold
- **Partial occupancy pricing** - Discounted rates for partially booked private rooms

### Addon Services
- **Unlimited addons** - Bike rentals, meals, extra beds, linen, etc.
- **Per-person pricing** - Charge addons per guest
- **Per-day pricing** - Charge addons per night
- **Room-specific addons** - Different services for different rooms
- **Maximum availability** - Limit quantity of each addon
- **Automatic application** - Addons automatically calculated in total

### Reports & Analytics
- **Earnings reports** - View income by date range
- **Room occupancy** - See which rooms are most popular
- **Room type profitability** - Compare earnings by room type
- **Charts & graphs** - Visual representation of data
- **Export capabilities** - Download reports for external analysis

### Synchronization
- **iCal export** - Each room provides .ics calendar URL
- **iCal import** - Import bookings from external calendars
- **Multiple import URLs** - Sync with multiple booking platforms per room
- **Booking.com support** - Import/export via iCal
- **Airbnb support** - Two-way synchronization
- **Expedia/Hotels.com** - Import bookings (requires iCal URL from Expedia)
- **TripAdvisor** - Sync availability calendar

### Security Features
- **SQL injection protection** - All queries use prepared statements
- **CSRF protection** - Nonce verification on all forms
- **XSS prevention** - Output escaping throughout
- **Capability checks** - Role-based access control
- **Input validation** - Strict validation on all user inputs
- **Direct file access protection** - All files protected against direct access

### User Roles & Permissions (Ensuite Module)
- **Staff management** - Create records for all staff members
- **Custom roles** - Define roles beyond default housekeeper/receptionist
- **Room cleaning schedule** - Automatic or manual cleaning assignments
- **Expense tracking** - Track expenses per room or globally
- **Access control** - Fine-tune permissions for different user roles

### Communication (Ensuite Module)
- **Thank-you notices** - Automated messages after guest departure
- **Email reminders** - Reminders for upcoming bookings
- **Customizable templates** - Edit email content and subject lines
- **Email logging** - Track all sent emails with status

### Internationalization
- **Translation ready** - Full gettext support
- **Language files** - Included .pot file for translations
- **Date/time localization** - Respects WordPress locale settings
- **Currency options** - USD, EUR, GBP, JPY, INR, and custom

---

## Installation

### Requirements
- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher
- cURL extension (for payment processing and iCal sync)
- OpenSSL extension (for secure connections)

### Standard Installation

1. **Download the plugin**
   ```bash
   git clone https://github.com/your-org/hostelpro.git
   ```

2. **Upload to WordPress**
   - Copy the `hostelpro` folder to `/wp-content/plugins/`
   - Or use WordPress admin: Plugins → Add New → Upload Plugin

3. **Activate the plugin**
   - Go to WordPress admin → Plugins
   - Find "Hostel PRO" and click "Activate"

4. **Run installation**
   - The plugin will automatically create required database tables on activation

### Manual Installation

```bash
# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Clone the repository
git clone https://github.com/your-org/hostelpro.git

# Set proper permissions
chown -R www-data:www-data hostelpro/
chmod -R 755 hostelpro/
```

---

## Quick Start Guide

Get your hostel booking system running in minutes:

### Step 1: Basic Configuration
1. Navigate to **Hostel PRO → Settings** in WordPress admin
2. Set your currency
3. Configure booking mode (PayPal, Stripe, or manual)
4. Set up PayPal/Stripe credentials if using online payments
5. Configure sender email and name for notifications

### Step 2: Create Rooms
1. Go to **Hostel PRO → Manage Rooms**
2. Click "Add Room"
3. Enter room details:
   - Title (e.g., "4-Bed Mixed Dorm")
   - Type (Private or Dorm)
   - Number of beds
   - Bathroom type (Ensuite/Shared)
   - Price per night
4. Add description and photos via WordPress editor
5. Save and repeat for all rooms

### Step 3: Configure Booking Form
1. Go to **Hostel PRO → Booking Form**
2. Add custom fields if needed (special requests, arrival time, etc.)
3. Arrange field order using drag & drop
4. Note the shortcode: `[hostelpro-booking]`

### Step 4: Publish Booking Form
Create a page and add the shortcode:
```
[hostelpro-booking]
```

Or use the room list with availability:
```
[hostelpro-list show_titles="1" show_descriptions="1"]
```

### Step 5: Set Unavailable Dates
1. Go to **Hostel PRO → Unavailable Dates**
2. Select dates when your property is closed
3. Mark specific rooms as unavailable for maintenance
4. Save changes

### Step 6: Configure Discounts (Optional)
1. Go to **Hostel PRO → Discounts & Surcharges**
2. Add seasonal pricing rules
3. Create coupon codes for promotions
4. Set weekday/weekend pricing differences

### Step 7: Test Booking
1. Visit your booking page as a customer
2. Select dates and room
3. Complete the booking form
4. Verify email notifications are received
5. Check booking appears in admin panel

**You're ready to accept guests!** 🎉

---

## Shortcodes Reference

### Main Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[hostelpro-booking]` | Display booking form |
| `[hostelpro-list]` | List all rooms with availability |
| `[hostelpro-book room_id="1"]` | Book button for specific room |
| `[hostelpro-calendar room_id="1"]` | Room availability calendar |
| `[hostelpro-calendar-overview]` | Overview calendar for all rooms |

### Form Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[hostelpro-form-start]` | Begin custom form |
| `[hostelpro-form-end]` | End custom form |
| `[hostelpro-field-static room_id]` | Room selection dropdown |
| `[hostelpro-field-static beds]` | Number of beds selector |
| `[hostelpro-field-static from_date]` | Arrival date picker |
| `[hostelpro-field-static to_date]` | Departure date picker |
| `[hostelpro-field-static contact_name]` | Guest name field |
| `[hostelpro-field-static contact_email]` | Guest email field |
| `[hostelpro-field field_id="1"]` | Custom field by ID |
| `[hostelpro-submit-button]` | Submit button |

### Conditional Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[hostelpro-if-extra-beds]Content[/hostelpro-if-extra-beds]` | Show if room has extra beds |
| `[hostelpro-if-beds]Content[/hostelpro-if-beds]` | Show bed selection info |
| `[hostelpro-room-addons]` | Display room addons |
| `[hostelpro-room-description]` | Display room description |

### Example: Custom Booking Form

```php
[hostelpro-form-start]
<h3>Book Your Stay</h3>
[hostelpro-field-static room_id]
[hostelpro-field-static from_date]
[hostelpro-field-static to_date]
[hostelpro-field-static beds]
[hostelpro-field-static contact_name]
[hostelpro-field-static contact_email]
[hostelpro-field-static contact_phone]
[hostelpro-field field_id="1"] <!-- Custom field -->
[hostelpro-submit-button "Reserve Now"]
[hostelpro-form-end]
```

---

## 🔄 Synchronization Guide

### Export Calendar (Outbound)

Each room generates an iCal URL for external services:

```
https://yoursite.com/?hostelpro_ical=1&room_id=ROOM_ID
```

**For download:**
```
https://yoursite.com/?hostelpro_ical=1&room_id=ROOM_ID&download=1
```

### Import Calendar (Inbound)

1. Go to **Hostel PRO → Manage Rooms**
2. Edit the room you want to sync
3. Find "iCal Import URL" field
4. Paste the external calendar URL
5. Save changes

The plugin will automatically import bookings from the external calendar.

### Supported Services

| Service | Import | Export | Notes |
|---------|--------|--------|-------|
| Booking.com | ✅ | ✅ | See Export Calendar section in Booking.com extranet |
| Airbnb | ✅ | ✅ | Full two-way sync supported |
| Expedia | ✅ | ⚠️ | Requires iCal URL from Expedia (hotel owners only) |
| Hotels.com | ✅ | ⚠️ | Via Expedia integration |
| TripAdvisor | ✅ | ✅ | Use iCal URL from extranet |
| VRBO | ✅ | ✅ | iCal synchronization supported |

**Note:** iCal sync typically updates every few hours. For real-time availability, consider channel manager solutions.

---

## Configuration Options

### General Settings

| Option | Description | Default |
|--------|-------------|---------|
| Currency | Payment currency (USD, EUR, GBP, etc.) | USD |
| Booking Mode | PayPal, Stripe, or Manual | Manual |
| PayPal Email | Your PayPal account email | - |
| Stripe Keys | Public and Secret keys | - |
| Advance Payment | Percentage or fixed amount | 100% |
| Min Stay | Minimum nights required | 1 |
| Max Stay | Maximum nights allowed | Unlimited |
| Max Date | How far ahead can book | 1 year |
| Cleanup Hours | Remove unpaid bookings after | 24 |

### Email Settings

| Option | Description |
|--------|-------------|
| Sender Name | Name in outgoing emails |
| Sender Email | Email address for notifications |
| Admin Email | Receive booking notifications |
| Email Templates | Customize guest and admin emails |

---

## File Structure

```
hostelpro/
├── hostelpro.php          # Main plugin file
├── controllers/           # Business logic
│   ├── ajax.php          # AJAX handlers
│   ├── bookings.php      # Booking management
│   ├── rooms.php         # Room management
│   ├── discounts.php     # Discount management
│   ├── addons.php        # Addon services
│   ├── calendars.php     # Calendar views
│   ├── form.php          # Booking form builder
│   ├── invoices.php      # Invoice generation
│   ├── reports.php       # Reports & analytics
│   ├── shortcodes.php    # Shortcode handlers
│   ├── sync.php          # iCal synchronization
│   └── roles.php         # User roles & permissions
├── models/               # Data layer
│   ├── booking.php       # Booking operations
│   ├── room.php          # Room operations
│   ├── payment.php       # Payment processing
│   ├── discount.php      # Discount operations
│   ├── addon.php         # Addon operations
│   ├── field.php         # Custom fields
│   └── hostelpro.php     # Main plugin class
├── views/                # Templates
│   ├── booking.html.php
│   ├── rooms.php
│   ├── bookings.php
│   └── ...
├── helpers/              # Utility functions
│   ├── htmlhelper.php
│   ├── filehelper.php
│   └── text-captcha.php
├── css/                  # Stylesheets
├── js/                   # JavaScript files
├── languages/            # Translation files
└── img/                  # Images
```

---

## Contributing

We welcome contributions from the community! Here's how you can help:

### Ways to Contribute

1. **Report Bugs** - Open an issue on GitHub
2. **Fix Bugs** - Submit a pull request
3. **Add Features** - Propose and implement new features
4. **Improve Documentation** - Help make docs clearer
5. **Translations** - Add support for your language
6. **Testing** - Test new releases and report issues

### Development Setup

```bash
# Fork the repository
git fork https://github.com/your-org/hostelpro.git

# Clone your fork
git clone https://github.com/YOUR_USERNAME/hostelpro.git

# Create a feature branch
git checkout -b feature/amazing-feature

# Make your changes
# ...

# Commit with clear messages
git commit -m "Add amazing feature"

# Push to your fork
git push origin feature/amazing-feature

# Open a Pull Request
```

### Coding Standards

- Follow WordPress Coding Standards
- Use prepared statements for all SQL queries
- Sanitize all inputs, escape all outputs
- Add nonce verification for state-changing operations
- Write clear, commented code
- Include unit tests for new features

### Security Guidelines

When contributing, please ensure:
- ✅ All user inputs are sanitized
- ✅ All outputs are escaped
- ✅ SQL queries use prepared statements
- ✅ Nonces verify state-changing actions
- ✅ Capability checks are in place
- ✅ No sensitive data in logs or error messages

---

## Changelog

### Version 2.0.7
- **Security Hardening** - Comprehensive security improvements
- SQL injection prevention with prepared statements
- CSRF protection with nonce verification
- XSS prevention with output escaping
- Direct file access protection on all files
- Input validation and sanitization
- Capability checks for admin functions
- Secure cookie handling
- Updated documentation

### Version 2.0.6
- Bug fixes and stability improvements

### Version 2.0.0
- Stripe payment integration
- Multiple booking mode
- Enhanced reporting
- Improved calendar views

*[View full changelog](CHANGELOG.md)*

---

## License

This project is licensed under the **GNU General Public License v2.0** (GPL-2.0).

```
Copyright (C) 2024 Hostel PRO Contributors

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
```

---

## Support

### Documentation
- [Help Page](#) - Built-in help in WordPress admin
- [Knowledge Base](#) - Articles and tutorials
- [FAQ](#) - Frequently asked questions

### Community Support
- [GitHub Issues](https://github.com/your-org/hostelpro/issues) - Bug reports and feature requests
- [WordPress Support Forum](https://wordpress.org/support/plugin/hostelpro/) - Community support

### Premium Support
For priority support and custom development:
- Email: support@wp-hostel.com
- Website: https://wp-hostel.com

**Response Time:**
- Community Support: 3-5 business days
- Premium Support: 24-48 hours

---

## Credits

### Contributors
- Original Author: Kiboko Labs
- Current Maintainers: Kiboko Labs
- All community contributors

### Libraries & Dependencies
- jQuery UI Datepicker
- jQuery Validate Plugin
- Stripe PHP Library
- WordPress Core APIs

### Translators
Thanks to all contributors who have translated Hostel PRO into multiple languages.

---

## Requirements

### Server Requirements
- **Web Server:** Apache or Nginx
- **PHP:** 8.0 or higher
- **MySQL:** 5.6 or higher (5.7+ recommended)
- **WordPress:** 5.0 or higher

### PHP Extensions
- cURL (for payments and iCal sync)
- OpenSSL (for secure connections)
- JSON (for API interactions)
- MBString (for international characters)

### Recommended
- SSL certificate for secure bookings
- PHP OPcache for better performance
- Regular backups of your WordPress database

---

### Reporting Security Issues
If you discover a security vulnerability, please report it responsibly:

1. **Do NOT** disclose publicly
2. Include detailed reproduction steps
3. Allow reasonable time for fix before disclosure

We take all security reports seriously and will respond promptly.

---

## Roadmap

### Planned Features
- [ ] Multi-language booking forms
- [ ] Advanced channel manager integration
- [ ] Mobile app for property management
- [ ] Automated pricing rules
- [ ] Guest portal for booking management
- [ ] Advanced reporting dashboard
- [ ] API for third-party integrations
- [ ] Multi-property support

### Under Consideration
- WooCommerce integration
- Membership/loyalty programs
- Automated review requests
- SMS notifications
- Payment plan options

Have ideas? [Open an issue](https://github.com/pimteam/hostelpro/issues) to discuss!

---

## Contact

- **Website:** https://wp-hostel.com
- **Email:** info@wp-hostel.com
- **GitHub:** https://github.com/pimteam/hostelpro

---

## Disclaimer

This plugin is provided "as is" without warranty of any kind. The authors are not liable for any damages arising from the use of this software.

We have no affiliation with "Hotel Master" theme or any other theme sold on ThemeForest or elsewhere.

iCal synchronization depends on third-party services providing valid iCal URLs. We cannot guarantee compatibility with all booking platforms.

---

**Made with ❤️ for hostel and B&B owners worldwide**

*Last updated: March 2026*

# Provenance Table Plugin for Omeka

A comprehensive provenance tracking plugin for Omeka, specifically designed for coin collections but adaptable to any collection requiring detailed ownership history tracking.

## Description

The Provenance Table Plugin adds a powerful, user-friendly interface for recording and displaying provenance information for items in your Omeka collection. It creates a dedicated "Provenance" tab in the item edit form where you can add multiple provenance entries with detailed information about each owner, transaction, and time period.

## Features

- **Comprehensive Data Fields**: Track owner names, dates, locations, sources, transaction types, prices, and detailed notes
- **Easy-to-Use Interface**: Add, edit, and delete provenance entries with a simple table interface
- **Drag-and-Drop Ordering**: Reorder provenance entries by dragging rows to reflect chronological sequence
- **Public Display**: Optional display of provenance tables on public item pages
- **Flexible Configuration**: Choose which fields to display and whether to show empty provenance sections
- **Database Storage**: All provenance data is stored in a dedicated database table for efficient querying
- **Responsive Design**: Works on desktop and mobile devices

## Data Fields

Each provenance entry can include:

- **Owner/Holder** (required): Name of the person or institution that owned/held the item
- **Date From**: Start date of ownership (flexible format: YYYY or YYYY-MM-DD)
- **Date To**: End date of ownership (flexible format: YYYY or YYYY-MM-DD)
- **Location**: Geographic location where the item was held
- **Source**: Where/how this provenance information was obtained
- **Transaction Type**: Purchase, Gift, Inheritance, Loan, Auction, or Unknown
- **Price**: Transaction price (if applicable)
- **Notes**: Additional details, context, or documentation

## Requirements

- Omeka 2.0 or higher
- PHP 5.6 or higher
- MySQL 5.0 or higher
- jQuery (included with Omeka)
- jQuery UI Sortable (included with Omeka)

## Installation

1. Download the plugin and extract to your Omeka `plugins` directory
2. Rename the folder to `ProvenanceTable` (if not already named)
3. Log into your Omeka admin panel
4. Navigate to Settings ’ Plugins
5. Click "Install" next to Provenance Table
6. Configure plugin settings as desired
7. Click "Save Changes"

For detailed installation instructions, see [INSTALL.txt](INSTALL.txt).

## Usage

### Adding Provenance Information

1. Navigate to an item in your Omeka admin panel
2. Click the "Provenance" tab in the item edit form
3. Click "Add Entry" to create a new provenance entry
4. Fill in the fields (at minimum, the Owner/Holder field is required)
5. Add additional entries as needed
6. Drag rows to reorder entries chronologically
7. Save the item

### Deleting Entries

Click the "Delete" button on any row to remove that provenance entry.

### Reordering Entries

Click and drag the order number (left column) to reorder entries. The table will automatically update numbering.

### Public Display

If enabled in plugin settings, provenance tables will automatically appear on public item pages below the standard item metadata.

## Configuration

Navigate to Settings ’ Plugins ’ Provenance Table ’ Configure to access:

- **Display on Public Pages**: Enable/disable public display of provenance tables
- **Show Empty Tables**: Choose whether to show the provenance section when no data exists
- **Default Fields**: Configure which fields to display (comma-separated list)

## Database Schema

The plugin creates a table `omeka_provenance_entries` with the following structure:

- `id`: Unique entry identifier
- `item_id`: Reference to the Omeka item
- `entry_order`: Sort order for entries
- `owner_name`: Owner/holder name
- `date_from`: Ownership start date
- `date_to`: Ownership end date
- `location`: Geographic location
- `source`: Information source
- `transaction_type`: Type of transaction
- `price`: Transaction price
- `notes`: Additional notes
- `created`: Timestamp of entry creation
- `modified`: Timestamp of last modification

## Support

For issues, questions, or feature requests:

- GitHub Issues: [https://github.com/7maram7/omeka-ProvenanceTablePlugin/issues](https://github.com/7maram7/omeka-ProvenanceTablePlugin/issues)
- Omeka Forums: [https://forum.omeka.org/](https://forum.omeka.org/)

## License

This plugin is licensed under the GNU General Public License v3.0 (GPLv3).

## Credits

Developed for Omeka coin collection management.

## Version History

- **1.0.0** (2025): Initial release
  - Complete provenance tracking system
  - Admin interface with drag-and-drop ordering
  - Public display options
  - Configurable field display

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues on GitHub.

## Testing

For testing instructions and test cases, see [TESTING.md](TESTING.md).

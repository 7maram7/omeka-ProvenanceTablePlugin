# Provenance Table Plugin - Quick Start Guide

Get started with the Provenance Table Plugin in just a few minutes!

## Installation (2 minutes)

1. Upload the plugin folder to `/path/to/omeka/plugins/ProvenanceTable/`
2. Go to **Settings ’ Plugins** in your Omeka admin panel
3. Click **Install** next to "Provenance Table"
4. Click **Configure** and set your preferences
5. Click **Save Changes**

Done! The plugin is now active.

## First Steps (5 minutes)

### Adding Your First Provenance Entry

1. **Navigate to an item**
   - Go to Items in your admin panel
   - Click on any existing item (or create a new one)

2. **Open the Provenance tab**
   - In the item edit form, you'll see a new "Provenance" tab
   - Click on it

3. **Add an entry**
   - Click the green **Add Entry** button
   - A new row appears in the table

4. **Fill in the information**
   - **Owner/Holder** (required): Enter the owner's name, e.g., "John Smith"
   - **Date From**: Enter the start date, e.g., "1950" or "1950-06-15"
   - **Date To**: Enter the end date, e.g., "1975" or "1975-12-31"
   - **Location**: Enter location, e.g., "New York, NY"
   - **Source**: How you know this info, e.g., "Auction catalog"
   - **Transaction Type**: Select from dropdown (Purchase, Gift, etc.)
   - **Price**: Enter price if known, e.g., "$500 USD"
   - **Notes**: Add any additional information

5. **Save the item**
   - Scroll down and click **Save Changes**
   - Your provenance entry is now saved!

### Adding Multiple Entries

For items with complex ownership histories:

1. Click **Add Entry** for each owner/holder
2. Fill in the information for each entry
3. Entries are displayed in the order you add them
4. Click **Save Changes** to save all entries

### Reordering Entries

If you need to change the chronological order:

1. Click and hold on the **order number** (left column)
2. Drag the row up or down
3. Release to drop it in the new position
4. The numbers automatically update
5. Click **Save Changes** to save the new order

### Deleting Entries

To remove an entry:

1. Click the **Delete** button on the row you want to remove
2. Confirm the deletion
3. The row is removed immediately
4. Click **Save Changes** to finalize

## Common Use Cases

### Example 1: Coin Provenance

For a Roman coin with known ownership history:

**Entry 1:**
- Owner: "Private Collector, Rome"
- Date From: "1850"
- Date To: "1890"
- Location: "Rome, Italy"
- Source: "Estate records"
- Transaction Type: "Unknown"
- Notes: "Mentioned in family papers"

**Entry 2:**
- Owner: "Giovanni Rossi"
- Date From: "1890"
- Date To: "1920"
- Location: "Florence, Italy"
- Source: "Rossi collection catalog"
- Transaction Type: "Inheritance"
- Notes: "Inherited from uncle's collection"

**Entry 3:**
- Owner: "American Numismatic Society"
- Date From: "1920"
- Date To: "Present"
- Location: "New York, NY"
- Source: "ANS acquisition records"
- Transaction Type: "Purchase"
- Price: "$1,200 USD"
- Notes: "Purchased at auction, lot 47"

### Example 2: Artwork with Single Owner

For a painting with one known owner:

- Owner: "Museum of Fine Arts"
- Date From: "1965"
- Date To: "Present"
- Location: "Boston, MA"
- Source: "Museum records"
- Transaction Type: "Gift"
- Notes: "Donated by the Smith family foundation"

### Example 3: Archaeological Artifact

For an artifact with gaps in history:

**Entry 1:**
- Owner: "Unknown"
- Date From: "1800s"
- Date To: "1950"
- Location: "Unknown"
- Notes: "Provenance unknown before 1950"

**Entry 2:**
- Owner: "Dr. James Anderson"
- Date From: "1950"
- Date To: "1985"
- Location: "London, UK"
- Source: "Personal correspondence"
- Transaction Type: "Purchase"
- Notes: "Purchased from private dealer"

**Entry 3:**
- Owner: "British Museum"
- Date From: "1985"
- Date To: "Present"
- Location: "London, UK"
- Source: "Museum acquisition records"
- Transaction Type: "Gift"

## Configuration Options

Access via **Settings ’ Plugins ’ Provenance Table ’ Configure**

### Display on Public Pages
- **Checked**: Provenance tables appear on public item pages
- **Unchecked**: Provenance only visible in admin panel

**Recommendation**: Check this if you want to share provenance with visitors

### Show Empty Tables
- **Checked**: Shows "No provenance available" message for items without data
- **Unchecked**: Hides provenance section entirely if no data exists

**Recommendation**: Uncheck this to keep public pages clean

### Default Fields
Customize which fields appear in the table (comma-separated):

**Default**: `owner_name,date_from,date_to,location,source,notes`

**All available fields**:
- `owner_name`
- `date_from`
- `date_to`
- `location`
- `source`
- `transaction_type`
- `price`
- `notes`

**Example custom configuration**:
```
owner_name,date_from,date_to,transaction_type,price
```

## Tips and Best Practices

### Date Formatting
- Use YYYY for year only: `1950`
- Use YYYY-MM-DD for full dates: `1950-06-15`
- Use circa when uncertain: `c. 1950` or `ca. 1950`
- Leave blank if unknown

### Handling Gaps in History
- Create entries for "Unknown" periods
- Use the Notes field to explain gaps
- Document what you don't know as well as what you do

### Source Documentation
- Always record where the information came from
- Include specific references (auction lot numbers, page numbers)
- Note if information is based on oral history or unverified

### Privacy Considerations
- Be mindful of living persons' privacy
- Consider using "Private Collector" instead of names for recent owners
- Check with your institution's policies

### Chronological Order
- Always order entries from oldest to most recent
- Use drag-and-drop to maintain chronological sequence
- Most recent ownership typically listed last

## Viewing Provenance

### Admin View
All provenance entries are visible when viewing any item in the admin panel

### Public View (if enabled)
Provenance appears as a formatted table on the public item page, typically after standard metadata

### Exporting Data
Provenance data is stored in the database and can be exported along with item data using Omeka's standard export features

## Keyboard Shortcuts

- **Tab**: Move to next field
- **Shift+Tab**: Move to previous field
- **Enter**: Submit form (when not in a text area)

## Getting Help

### Documentation
- Full documentation: [README.md](README.md)
- Installation help: [INSTALL.txt](INSTALL.txt)
- Testing guide: [TESTING.md](TESTING.md)

### Support
- GitHub Issues: [https://github.com/7maram7/omeka-ProvenanceTablePlugin/issues](https://github.com/7maram7/omeka-ProvenanceTablePlugin/issues)
- Omeka Forums: [https://forum.omeka.org/](https://forum.omeka.org/)

## What's Next?

Now that you've learned the basics:

1. Add provenance to your existing items
2. Customize the plugin settings for your needs
3. Consider creating documentation standards for your team
4. Explore the public display options
5. Review the full documentation for advanced features

Happy cataloging!

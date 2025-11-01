# Provenance Table Plugin - Testing Guide

Comprehensive testing procedures for the Provenance Table Plugin.

## Pre-Installation Testing

### Environment Check
- [ ] Verify Omeka version is 2.0 or higher
- [ ] Verify PHP version is 5.6 or higher
- [ ] Verify MySQL is installed and accessible
- [ ] Verify web server has write permissions on plugins directory

## Installation Testing

### Basic Installation
1. [ ] Upload plugin files to `plugins/ProvenanceTable/`
2. [ ] Navigate to Settings ’ Plugins
3. [ ] Verify plugin appears in available plugins list
4. [ ] Click "Install"
5. [ ] Verify installation completes without errors
6. [ ] Verify plugin status shows as "Active"

### Database Creation
1. [ ] Access your MySQL database
2. [ ] Verify table `{prefix}provenance_entries` was created
3. [ ] Verify table has correct columns:
   - [ ] id (primary key)
   - [ ] item_id
   - [ ] entry_order
   - [ ] owner_name
   - [ ] date_from
   - [ ] date_to
   - [ ] location
   - [ ] source
   - [ ] transaction_type
   - [ ] price
   - [ ] notes
   - [ ] created
   - [ ] modified

### Configuration
1. [ ] Click "Configure" next to plugin
2. [ ] Verify all config options appear
3. [ ] Change each setting and save
4. [ ] Verify settings are persisted after page reload

## Functional Testing

### Admin Interface

#### Tab Display
1. [ ] Navigate to Items ’ Add an Item
2. [ ] Verify "Provenance" tab appears
3. [ ] Click the Provenance tab
4. [ ] Verify table interface loads correctly

#### Adding Entries
1. [ ] Click "Add Entry" button
2. [ ] Verify new row appears in table
3. [ ] Fill in all fields with test data
4. [ ] Click "Add Entry" again
5. [ ] Verify second row appears
6. [ ] Verify both rows maintain their data

#### Field Validation
1. [ ] Try to save item with empty owner_name field
2. [ ] Verify appropriate validation (owner_name is required)
3. [ ] Fill in owner_name
4. [ ] Save successfully
5. [ ] Test each field with various inputs:
   - [ ] owner_name: Special characters, long text
   - [ ] date_from: Various formats (YYYY, YYYY-MM-DD)
   - [ ] date_to: Various formats
   - [ ] location: International characters
   - [ ] source: Long text
   - [ ] transaction_type: Each dropdown option
   - [ ] price: Various currencies and formats
   - [ ] notes: Very long text (500+ characters)

#### Drag-and-Drop Ordering
1. [ ] Create 3-5 entries
2. [ ] Save the item
3. [ ] Return to edit the item
4. [ ] Click and drag the first entry
5. [ ] Drop it in the middle of the list
6. [ ] Verify order numbers update automatically
7. [ ] Save the item
8. [ ] Reload the page
9. [ ] Verify new order is persisted

#### Deleting Entries
1. [ ] Create multiple entries
2. [ ] Click "Delete" on the middle entry
3. [ ] Verify confirmation dialog appears
4. [ ] Confirm deletion
5. [ ] Verify row is removed immediately
6. [ ] Verify order numbers update
7. [ ] Save the item
8. [ ] Verify deletion persisted

#### Editing Existing Entries
1. [ ] Create and save an entry
2. [ ] Return to edit the item
3. [ ] Verify entry data appears correctly
4. [ ] Modify several fields
5. [ ] Save the item
6. [ ] Return to edit again
7. [ ] Verify changes were saved

### Public Display

#### Public View (Enabled)
1. [ ] Enable "Display on Public Pages" in config
2. [ ] Create an item with provenance data
3. [ ] Save the item
4. [ ] View item on public site
5. [ ] Verify provenance section appears
6. [ ] Verify table displays correctly
7. [ ] Verify all data is visible
8. [ ] Test on different screen sizes (responsive)

#### Public View (Disabled)
1. [ ] Disable "Display on Public Pages" in config
2. [ ] View same item on public site
3. [ ] Verify provenance section does NOT appear

#### Empty Table Display
1. [ ] Enable "Show Empty Tables" in config
2. [ ] Create item with NO provenance data
3. [ ] View on public site
4. [ ] Verify message "No provenance information available" appears
5. [ ] Disable "Show Empty Tables" in config
6. [ ] Reload public page
7. [ ] Verify provenance section does NOT appear

### CSS and JavaScript

#### Admin Styles
1. [ ] Navigate to Provenance tab
2. [ ] Verify table has proper styling
3. [ ] Verify buttons are styled correctly
4. [ ] Verify drag handle cursor changes on hover
5. [ ] Test in different browsers:
   - [ ] Chrome
   - [ ] Firefox
   - [ ] Safari
   - [ ] Edge

#### Public Styles
1. [ ] View item with provenance on public site
2. [ ] Verify table is styled appropriately
3. [ ] Verify responsive design works on mobile
4. [ ] Test in different browsers
5. [ ] Test print styles (print preview)

#### JavaScript Functionality
1. [ ] Verify jQuery loads without errors (check browser console)
2. [ ] Verify drag-and-drop works smoothly
3. [ ] Verify "Add Entry" button responds immediately
4. [ ] Verify "Delete" button shows confirmation
5. [ ] Verify no JavaScript errors in console

## Data Integrity Testing

### Database Persistence
1. [ ] Create item with 3 provenance entries
2. [ ] Save the item
3. [ ] Query database directly:
   ```sql
   SELECT * FROM omeka_provenance_entries WHERE item_id = [item_id];
   ```
4. [ ] Verify 3 rows exist
5. [ ] Verify data matches what was entered
6. [ ] Verify timestamps are set

### Multiple Items
1. [ ] Create Item A with 2 entries
2. [ ] Create Item B with 3 entries
3. [ ] Verify Item A only shows its 2 entries
4. [ ] Verify Item B only shows its 3 entries
5. [ ] Delete Item A
6. [ ] Verify Item B entries remain intact

### Data Modification
1. [ ] Create item with entry
2. [ ] Note the entry ID from database
3. [ ] Edit the entry
4. [ ] Save
5. [ ] Verify entry ID remains the same (update, not insert)
6. [ ] Verify `modified` timestamp updated

### Data Deletion
1. [ ] Create item with 3 entries
2. [ ] Delete one entry via UI
3. [ ] Save
4. [ ] Verify only 2 entries remain in database
5. [ ] Verify deleted entry is completely removed

## Edge Cases and Error Handling

### Empty Owner Name
1. [ ] Create entry with empty owner_name
2. [ ] Try to save
3. [ ] Verify validation prevents save (or entry is skipped)

### Very Long Text
1. [ ] Enter 10,000 characters in notes field
2. [ ] Save
3. [ ] Verify data is stored correctly
4. [ ] Verify display doesn't break layout

### Special Characters
1. [ ] Enter special characters in all fields:
   - Unicode characters (é, ñ, -‡)
   - HTML characters (<, >, &)
   - Quotes (' and ")
2. [ ] Save
3. [ ] Verify characters display correctly
4. [ ] Verify no XSS vulnerabilities (HTML is escaped)

### SQL Injection Testing
1. [ ] Enter SQL-like strings:
   - `'; DROP TABLE omeka_provenance_entries; --`
   - `<script>alert('XSS')</script>`
2. [ ] Save
3. [ ] Verify data is properly escaped
4. [ ] Verify no database errors occur

### Concurrent Editing
1. [ ] Open item in two browser tabs
2. [ ] Edit provenance in tab 1, save
3. [ ] Edit provenance in tab 2, save
4. [ ] Verify which changes persist (should be tab 2)

### No JavaScript Scenario
1. [ ] Disable JavaScript in browser
2. [ ] Navigate to item edit page
3. [ ] Verify basic functionality still works
4. [ ] Re-enable JavaScript

## Browser Compatibility

Test in the following browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

For each browser, verify:
- [ ] Table displays correctly
- [ ] Drag-and-drop works
- [ ] Add/Delete functions work
- [ ] Styling appears correct

## Performance Testing

### Large Datasets
1. [ ] Create item with 50 provenance entries
2. [ ] Verify page loads in reasonable time (< 3 seconds)
3. [ ] Verify drag-and-drop still works smoothly
4. [ ] Verify save completes without timeout

### Multiple Items with Provenance
1. [ ] Create 100 items, each with 5 provenance entries
2. [ ] Verify admin items list loads normally
3. [ ] Verify editing any item works normally
4. [ ] Check database query performance

## Upgrade Testing

### Plugin Deactivation/Reactivation
1. [ ] Create items with provenance data
2. [ ] Deactivate plugin
3. [ ] Verify provenance tab disappears
4. [ ] Verify items still accessible
5. [ ] Reactivate plugin
6. [ ] Verify provenance data still exists
7. [ ] Verify tab reappears

### Uninstallation
1. [ ] Create test item with provenance data
2. [ ] Uninstall plugin
3. [ ] Verify provenance table is dropped from database
4. [ ] Verify plugin options are removed
5. [ ] Verify items are not affected (still exist)

## Accessibility Testing

### Keyboard Navigation
1. [ ] Use Tab key to navigate through fields
2. [ ] Verify all inputs are accessible
3. [ ] Verify buttons can be activated with Enter/Space
4. [ ] Test with screen reader software

### Color Contrast
1. [ ] Use browser dev tools to check color contrast
2. [ ] Verify all text meets WCAG AA standards
3. [ ] Test with high contrast mode enabled

## Documentation Testing

### README Accuracy
1. [ ] Follow all examples in README.md
2. [ ] Verify they work as described
3. [ ] Check all links in documentation

### Installation Instructions
1. [ ] Follow INSTALL.txt step-by-step on a fresh Omeka install
2. [ ] Verify each step is accurate
3. [ ] Note any missing or unclear instructions

### Quick Start Guide
1. [ ] Give QUICKSTART.md to a new user
2. [ ] Observe their experience
3. [ ] Note any confusion or errors

## Reporting Issues

When reporting bugs found during testing, include:
- Omeka version
- PHP version
- Browser and version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Error messages (from browser console and Omeka logs)

## Test Results Template

```
Plugin Version: 1.0.0
Omeka Version: 2.8
PHP Version: 7.4
MySQL Version: 5.7
Date Tested: YYYY-MM-DD
Tester: [Name]

Installation: PASS/FAIL
Admin Interface: PASS/FAIL
Public Display: PASS/FAIL
Data Integrity: PASS/FAIL
Browser Compatibility: PASS/FAIL
Performance: PASS/FAIL

Issues Found:
1. [Description of issue]
2. [Description of issue]

Notes:
[Any additional observations]
```

## Continuous Testing

Recommended testing schedule:
- After any code changes
- Before each release
- After Omeka updates
- Quarterly for production installations

## Automated Testing

Consider implementing:
- Unit tests for PHP functions
- Integration tests for database operations
- Selenium tests for UI interactions
- Performance benchmarks

## Success Criteria

The plugin passes testing when:
- [ ] All installation tests pass
- [ ] All functional tests pass
- [ ] No critical bugs found
- [ ] Works in all supported browsers
- [ ] Performance is acceptable
- [ ] Documentation is accurate
- [ ] Accessibility standards met

# WordPress base localizations

This directory is for "default" localization files. These will be copied to the `wordpress/wp-content/languages/` directory.

If there is already a file for the locale, it will not be replaced.

### How-To

1. Make a copy of the `en_US.po` file, naming it with your locale code.
2. Use POEdit to edit the file.
3. Save the file with POEdit. This will also generate a `.mo` file.
4. Copy both the `.po` and `.mo` files back into this directory.

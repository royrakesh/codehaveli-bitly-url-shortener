#!/bin/bash

# Script to create a zip file of what will be included in the WordPress plugin
# This mimics what 10up/action-wordpress-plugin-deploy does - includes everything
# except files matching patterns in .distignore

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

ZIP_FILE="codehaveli-bitly-url-shortener.zip"
PLUGIN_SLUG="codehaveli-bitly-url-shortener"

# Clean up any existing zip
rm -f "$ZIP_FILE"

# Check if .distignore exists
if [ ! -f ".distignore" ]; then
    echo "Error: .distignore file not found"
    exit 1
fi

echo "Creating plugin zip file: $ZIP_FILE"
echo "Excluding files matching patterns in .distignore..."

# Read .distignore and build exclusion patterns for zip
EXCLUDE_ARGS=()
while IFS= read -r line || [ -n "$line" ]; do
    # Skip comments and empty lines
    if [[ "$line" =~ ^[[:space:]]*# ]] || [[ -z "${line// }" ]]; then
        continue
    fi
    # Remove leading/trailing whitespace
    original_pattern=$(echo "$line" | xargs)
    if [ -n "$original_pattern" ]; then
        # Check if pattern originally had trailing slash (indicates directory)
        is_directory=false
        if [[ "$original_pattern" == *"/" ]]; then
            is_directory=true
        fi
        
        # Remove trailing slash for processing
        pattern="${original_pattern%/}"
        
        # Convert .distignore patterns to zip -x patterns
        if [ "$is_directory" = true ] || [ -d "$pattern" ] 2>/dev/null; then
            # Directory pattern: exclude the directory and all its contents
            EXCLUDE_ARGS+=("-x" "$pattern/*")
            EXCLUDE_ARGS+=("-x" "$pattern")
        elif [[ "$pattern" == *"*"* ]]; then
            # Pattern contains wildcard (e.g., *.log, webpack.*.js)
            EXCLUDE_ARGS+=("-x" "$pattern")
        else
            # Regular file or pattern - exclude the file itself
            EXCLUDE_ARGS+=("-x" "$pattern")
            # If it doesn't contain a dot and has a slash, might be a directory path
            if [[ "$pattern" == *"/"* ]] && [[ "$pattern" != *"."* ]]; then
                EXCLUDE_ARGS+=("-x" "$pattern/*")
            fi
        fi
    fi
done < .distignore

# Also exclude the script itself and the zip file being created
EXCLUDE_ARGS+=("-x" "create-plugin-zip.sh")
EXCLUDE_ARGS+=("-x" "$ZIP_FILE")

# Create zip file excluding the patterns from .distignore
if [ ${#EXCLUDE_ARGS[@]} -gt 0 ]; then
    zip -r "$ZIP_FILE" . "${EXCLUDE_ARGS[@]}" > /dev/null 2>&1
else
    zip -r "$ZIP_FILE" . > /dev/null 2>&1
fi

if [ -f "$ZIP_FILE" ]; then
    ZIP_SIZE=$(du -h "$ZIP_FILE" | cut -f1)
    FILE_COUNT=$(unzip -l "$ZIP_FILE" | tail -1 | awk '{print $2}')
    echo "✓ Created $ZIP_FILE ($ZIP_SIZE)"
    echo "  Contains $FILE_COUNT files that will be deployed to WordPress.org"
    echo ""
    echo "Files included:"
    unzip -l "$ZIP_FILE" | grep -E "^[[:space:]]*[0-9]+" | awk '{print $4}' | grep -v "^$" | head -20
    echo "..."
    echo ""
    echo "This zip represents what will be pushed to WordPress.org"
else
    echo "✗ Failed to create zip file"
    exit 1
fi

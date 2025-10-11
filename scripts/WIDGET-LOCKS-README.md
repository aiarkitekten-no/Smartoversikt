# Widget Protection Scripts

## Quick Reference

### Status & Overview
```bash
./scripts/widget-status.sh           # Show all widget lock status
```

### Lock Management
```bash
./scripts/unlock-widget.sh <name>    # Unlock widget for editing
./scripts/lock-widget.sh <name>      # Lock widget after editing
./scripts/lock-all-widgets.sh        # Lock ALL widgets (initial setup)
```

### Validation
```bash
./scripts/verify-widget-integrity.sh # Check HTML structure of all widgets
```

## Examples

### Edit a single widget safely
```bash
./scripts/unlock-widget.sh season-tree-lights
# ... edit resources/views/widgets/season-tree-lights.blade.php ...
./scripts/lock-widget.sh season-tree-lights "Added Spotify player"
```

### Check which widgets can be edited
```bash
./scripts/widget-status.sh | grep "🔓"
```

### Lock everything (protect production)
```bash
./scripts/lock-all-widgets.sh "Production freeze"
```

## How It Works

- Each widget has a `.lock` file when locked
- Lock file: `resources/views/widgets/.widget-name.lock`
- Pre-commit hook prevents committing locked widgets
- Only unlocked widgets can be modified

## Protection Status

Run `./scripts/widget-status.sh` to see:
- 🔒 Locked widgets (protected)
- 🔓 Unlocked widgets (can edit)
- 📊 Protection percentage

## For More Details

See: `../AI-learned/PER-WIDGET-LOCK-SYSTEM.md`

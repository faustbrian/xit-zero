# Basic Commands

## show - Display Tasks

View tasks from `.xit` files in the current directory.

```bash
# Show all tasks
xit show

# Show with task IDs for reference
xit show --show-id

# Show from specific file
xit show --file tasks.xit

# Filter by status
xit show --status open
xit show --status checked
xit show --status ongoing
xit show --status obsolete
xit show --status inquestion

# Include subdirectories
xit show --subdir
```

**Output symbols:**
- `☐` Open task
- `☑` Checked/completed task
- `⊙` Ongoing task
- `⊘` Obsolete task
- `?` Task in question

**Examples:**

```bash
# Quick daily review
xit show --status open --show-id

# Review completed work
xit show --status checked

# Check all tasks across project
xit show --subdir --show-id
```

## stats - Task Statistics

View task counts and completion rates.

```bash
# Stats for all .xit files in current directory
xit stats

# Stats for specific file
xit stats --file work.xit

# Stats including subdirectories
xit stats --subdir
```

**Example output:**

```
Total tasks:      42
Open:             15
Checked:          20
Ongoing:          3
Obsolete:         2
In question:      2
Completion rate:  47.6%
```

## add - Create New Tasks

Add tasks to `.xit` files.

```bash
# Simple task
xit add "Buy groceries"

# Task with priority (!, !!, !!!)
xit add "! Review PR #urgent"

# Task with tags and due date
xit add "Deploy to production #devops -> 2025-12-31"

# Task in specific file
xit add "Weekly meeting notes" --file work.xit
```

**Task creation patterns:**

```bash
# Priority levels
xit add "! Important task"         # Priority 1
xit add "!! Very important task"   # Priority 2
xit add "!!! Critical task"        # Priority 3

# Tags
xit add "Fix auth bug #urgent #security"

# Due dates
xit add "Quarterly review -> 2025-Q4"
xit add "Sprint planning -> 2025-W45"
xit add "Release v2.0 -> 2025-12-15"

# Combined
xit add "!! Deploy hotfix #urgent #production -> today"
```

**Tips:**

- Tasks are added as open `[ ]` by default
- Use quotes for descriptions with spaces
- Multiple tags can be added with multiple `#` symbols
- See [scheduling.md](scheduling.md) for date format options
- New tasks append to the file (or create it if missing)

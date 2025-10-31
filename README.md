# xit - Plain-Text Task Management CLI

A Laravel Zero CLI application for managing tasks in the [xit format](https://github.com/jotaen/xit) - plain-text todos with checkboxes.

## Features

- ✅ **Full xit v1.1 specification compliance** - All checkbox types, priorities, tags, due dates
- ✅ **All 12 commands implemented** - show, stats, add, mark, reschedule, rm, move, recur, edit, prio, tag, untag
- ✅ **Batch operations** - Mark/reschedule/remove/move multiple tasks at once
- ✅ **Smart date parsing** - today, tomorrow, +1w, +2m, yyyy-mm-dd, etc.
- ✅ **Task IDs** - Reference tasks by sequential IDs across all files
- ✅ **Auto-discovery** - Automatically finds `.xit` files in current directory
- ✅ **Colorized output** - Status-specific colors for better readability
- ✅ **Task statistics** - See completion rates and task breakdowns
- ✅ **Recurring tasks** - Create weekly/monthly/daily recurring instances

## Installation

```bash
composer install
php xit app:build
```

## Usage

### Show Tasks

```bash
# Show all tasks in current directory
php xit show

# Show tasks from specific file
php xit show --file tasks.xit

# Show only open tasks
php xit show --status open

# Show tasks with IDs for reference
php xit show --show-id

# Include subdirectories
php xit show --subdir
```

### Add Tasks

```bash
# Add a simple task
php xit add "Buy groceries"

# Add task with priority, tags, and due date
php xit add "!! Important meeting -> 2025-12-15 #work" --file work.xit
```

### Mark Tasks

```bash
# Mark task #5 as done
php xit mark 5 --done

# Mark multiple tasks as done
php xit mark 2 3 4 5 6 --done

# Reopen a task
php xit mark 1 --open

# Mark tasks as obsolete
php xit mark 7 8 --obsolete

# Mark task as in question
php xit mark 9 --inquestion

# Mark task as ongoing
php xit mark 3 --ongoing
```

### Reschedule Tasks

```bash
# Set specific date for single task
php xit reschedule 5 --date 2025-12-31

# Set multiple tasks to today
php xit reschedule 2 3 4 --date today

# Set task to tomorrow
php xit reschedule 1 --date tomorrow

# Add one week to tasks
php xit reschedule 1 2 --date "+1w"
```

### Remove Tasks

```bash
# Remove single task (with confirmation)
php xit rm 5

# Remove multiple tasks
php xit rm 2 3 4 5

# Remove without confirmation
php xit rm 5 --force
```

### Move Tasks Between Files

```bash
# Move single task to another file
php xit move 5 --target other.xit

# Move multiple tasks
php xit move 2 3 4 --target done.xit
```

### Create Recurring Tasks

```bash
# Create 4 weekly instances of task #5
php xit recur 5 --interval 1w --count 4

# Create 5 bi-weekly instances
php xit recur 3 --interval 2w --count 5

# Monthly recurrence until end date
php xit recur 7 --interval 1m --end 2026-12-31

# Daily recurrence in specific file
php xit recur 2 --interval 1d --count 30 --target work.xit
```

### Edit Task Properties

```bash
# Change task description
php xit edit 5 "Updated task description"

# Set priority level (0-3)
php xit prio 3 2

# Remove priority
php xit prio 7 0

# Add tag to task
php xit tag 5 urgent

# Remove tag from task
php xit untag 5 urgent
```

### View Statistics

```bash
# Show task statistics
php xit stats

# Show stats for specific file
php xit stats --file work.xit
```

## XIT Format

The xit format is a plain-text specification for todos with these features:

### Status Checkboxes

```
[ ] Open task
[x] Checked/completed task
[@] Ongoing task
[~] Obsolete task
[?] Task in question
```

### Priorities

```
[ ] ! Important
[ ] !! Very important
[ ] !!! Critical
```

### Tags and Due Dates

```
[ ] Buy groceries #shopping -> 2025-11-01
[ ] Fix bug #urgent #work -> 2025-Q4
[ ] Review code #work -> 2025-W45
```

### Groups and Titles

```
Shopping
[ ] Buy milk
[ ] Buy bread

Work
[ ] Review PRs
[ ] Fix bugs
```

### Continuation Lines

```
[ ] Long task description
    that continues on the next line
    and can span multiple lines
```

## Examples

Create a task file:

```bash
echo "Shopping
[ ] Buy milk
[ ] Buy bread" > tasks.xit
```

View tasks:

```bash
php xit show --show-id
# [1] ☐ Buy milk
# [2] ☐ Buy bread
```

Mark task as done:

```bash
php xit mark 1 --done
php xit show
# ☑ Buy milk
# ☐ Buy bread
```

View statistics:

```bash
php xit stats
# Total tasks:      2
# Open:             1
# Checked:          1
# Completion rate:  50.0%
```

## Architecture

```
app/
├── Commands/          # All 12 xit commands
│   ├── ShowCommand.php
│   ├── StatsCommand.php
│   ├── AddCommand.php
│   ├── MarkCommand.php
│   ├── RescheduleCommand.php
│   ├── RmCommand.php
│   ├── MoveCommand.php
│   ├── RecurCommand.php
│   ├── EditCommand.php
│   ├── PrioCommand.php
│   ├── TagCommand.php
│   └── UntagCommand.php
├── Parser/            # Spec-compliant xit parser
│   ├── Task.php
│   ├── Tag.php
│   ├── DueDate.php
│   ├── TaskGroup.php
│   ├── XitFile.php
│   └── XitParser.php
└── Services/          # Utilities
    ├── DateParser.php
    ├── FileDiscovery.php
    └── TaskIndex.php
```

## Documentation

### Cookbook

Comprehensive guides for all features:

- **[CLI Syntax](cookbook/cli-syntax.md)** - ⚠️ Important syntax differences from original spec
- **[Basic Commands](cookbook/basic-commands.md)** - show, stats, add
- **[Task Management](cookbook/task-management.md)** - mark, edit, prio
- **[Scheduling](cookbook/scheduling.md)** - reschedule, recur, date formats
- **[Batch Operations](cookbook/batch-operations.md)** - multi-task operations
- **[Organization](cookbook/organization.md)** - move, tag, file management
- **[XIT Format](cookbook/xit-format.md)** - complete format specification

### Quick links

- **Getting started**: See [Basic Commands](cookbook/basic-commands.md)
- **Daily workflows**: See [Task Management](cookbook/task-management.md) and [Organization](cookbook/organization.md)
- **Date handling**: See [Scheduling](cookbook/scheduling.md)
- **Format spec**: See [XIT Format](cookbook/xit-format.md)

## License

MIT

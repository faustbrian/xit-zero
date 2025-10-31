# CLI Syntax Differences

This document outlines the minor syntax differences between this Laravel Zero implementation and the original xit CLI specification.

## ✅ Fully Supported Commands

All 12 core commands are fully implemented with complete functionality:

### Show Tasks
```bash
xit show                           # Show all tasks
xit show -f tasks.xit             # Show from specific file
xit show --status open            # Filter by status (open, checked, ongoing, obsolete, inquestion)
xit show --show-id                # Show with task IDs
xit show --subdir                 # Include subdirectories
```

### Statistics
```bash
xit stats                          # Show task statistics
xit stats -f work.xit             # Stats for specific file
```

### Add Tasks
```bash
xit add "Buy groceries"                                    # Simple task
xit add "!! Important meeting -> 2025-12-15 #work" -f work.xit  # With priority, date, tags
```

### Mark Tasks (Batch Support)
```bash
xit mark 5 --done                  # Mark task #5 as done
xit mark 2 3 4 5 6 --done         # Mark multiple tasks as done
xit mark 1 --open                  # Reopen a task
xit mark 7 8 --obsolete           # Mark tasks as obsolete
xit mark 9 --inquestion           # Mark task as in question
xit mark 3 --ongoing              # Mark task as ongoing
```

### Reschedule Tasks (Batch Support)
```bash
xit reschedule 5 --date 2025-12-31      # Specific date
xit reschedule 2 3 4 --date today       # Multiple tasks to today
xit reschedule 1 2 --date tomorrow      # Multiple tasks to tomorrow
xit reschedule 1 2 --date "+1w"         # Add one week offset
xit reschedule 1 --date "+2m"           # Add two months
```

**Smart Date Parsing:**
- Absolute: `2025-12-31`, `2025-12`, `2025`
- Relative: `today`, `tomorrow`
- Offsets: `+1d`, `+1w`, `+2m`, `+1y`, `-1w`

### Remove Tasks (Batch Support)
```bash
xit rm 5                           # Remove with confirmation
xit rm 2 3 4 5                    # Remove multiple tasks
xit rm 5 --force                  # Skip confirmation prompt
```

### Move Tasks (Batch Support)
```bash
xit move 5 --target other.xit              # Move single task
xit move 2 3 4 --target done.xit          # Move multiple tasks
```

### Create Recurring Tasks
```bash
xit recur 5 --interval 1w --count 4        # 4 weekly instances
xit recur 3 --interval 2w --count 5        # 5 bi-weekly instances
xit recur 7 --interval 1m --end 2026-12-31 # Monthly until end date
xit recur 2 --interval 1d --count 30 --target work.xit  # 30 daily instances to file
```

**Supported Intervals:**
- `1d` - daily
- `1w` - weekly
- `1m` - monthly
- `1y` - yearly

### Edit Task Properties
```bash
xit edit 5 "Updated task description"      # Change description
xit prio 3 2                               # Set priority level 2 (0-3)
xit prio 7 0                               # Remove priority
xit tag 5 urgent                           # Add #urgent tag
xit untag 5 urgent                         # Remove #urgent tag
```

## ❌ Syntax Differences from Original Spec

### 1. Global File Flag Position

**Original Spec:**
```bash
xit -f tasks.xit show
```

**This Implementation:**
```bash
xit show -f tasks.xit
```

**Reason:** Laravel Zero/Symfony Console processes command-specific options, not global options before the command name.

### 2. Reschedule Date Argument

**Original Spec:**
```bash
xit reschedule 5 2025-12-31
xit reschedule 2 3 4 today
```

**This Implementation:**
```bash
xit reschedule 5 --date 2025-12-31
xit reschedule 2 3 4 --date today
```

**Reason:** Symfony Console doesn't support optional positional arguments after variadic arguments (`ids*`). Using `--date` flag provides clearer API and better consistency with other flags.

### 3. Recur Command Short Flags

**Original Spec:**
```bash
xit recur 5 -i 1w -n 4
xit recur 3 -i 2w -n 5
xit recur 7 -i 1m -e 2026-12-31
xit recur 2 -i 1d -n 30 -t work.xit
```

**This Implementation:**
```bash
xit recur 5 --interval 1w --count 4
xit recur 3 --interval 2w --count 5
xit recur 7 --interval 1m --end 2026-12-31
xit recur 2 --interval 1d --count 30 --target work.xit
```

**Reason:**
- `-n` conflicts with Laravel's built-in `--no-interaction` flag
- `-i`, `-e`, `-t` could conflict with other built-in flags
- Long-form flags provide better clarity and self-documentation

## Summary

All core functionality is **100% implemented** with identical behavior. The differences are purely syntactic:

- Use `-f` flag **after** the command name, not before
- Use `--date` flag for reschedule instead of positional argument
- Use long-form flags (`--interval`, `--count`, etc.) for recur command

All batch operations, date parsing, file operations, and xit format features work exactly as specified.

# Task Management

## mark - Change Task Status

Update task checkboxes to different states.

```bash
# Mark as done
xit mark 5 --done

# Reopen task
xit mark 5 --open

# Mark as ongoing
xit mark 5 --ongoing

# Mark as obsolete
xit mark 5 --obsolete

# Mark as in question
xit mark 5 --inquestion
```

**Status transitions:**

| Flag | Symbol | Meaning |
|------|--------|---------|
| `--open` | `[ ]` | Task is open/pending |
| `--done` | `[x]` | Task is completed |
| `--ongoing` | `[@]` | Task is in progress |
| `--obsolete` | `[~]` | Task is no longer relevant |
| `--inquestion` | `[?]` | Task needs clarification |

**Common workflows:**

```bash
# Start working on a task
xit mark 12 --ongoing

# Complete it
xit mark 12 --done

# Reopen if more work needed
xit mark 12 --open

# Cancel obsolete tasks
xit mark 8 9 10 --obsolete
```

## edit - Update Task Description

Change the text of an existing task.

```bash
# Update task #5's description
xit edit 5 "New task description"

# Preserve existing tags and dates by including them
xit edit 5 "Updated task #urgent -> 2025-12-31"
```

**Important notes:**
- Task ID remains the same
- Priority, tags, and dates must be re-specified if you want to keep them
- Use quotes for descriptions with spaces

**Examples:**

```bash
# Simple edit
xit edit 3 "Review pull request"

# Edit with metadata preservation
xit edit 7 "!! Deploy hotfix #critical #production -> today"

# Fix typos
xit edit 12 "Fix authentication bug"
```

## prio - Set Task Priority

Change task priority level (0-3).

```bash
# Set priority to level 2 (very important)
xit prio 5 2

# Remove priority
xit prio 5 0

# Set to critical
xit prio 3 3
```

**Priority levels:**

| Level | Symbol | Meaning |
|-------|--------|---------|
| 0 | _(none)_ | No priority |
| 1 | `!` | Important |
| 2 | `!!` | Very important |
| 3 | `!!!` | Critical |

**Examples:**

```bash
# Escalate to critical
xit prio 8 3

# Downgrade priority
xit prio 15 1

# Clear priority after completion
xit mark 5 --done && xit prio 5 0
```

**Workflow tips:**

```bash
# Mark urgent tasks at priority 2+
xit prio 4 2
xit prio 7 3

# Use priority 1 for important but not urgent
xit prio 12 1

# View high-priority tasks
xit show --show-id | rg '!!'
```

## Combined workflows

**Task lifecycle example:**

```bash
# 1. Add new task
xit add "!! Fix auth bug #urgent #security -> tomorrow"

# 2. Start work
xit show --show-id  # Find task ID (e.g., 42)
xit mark 42 --ongoing

# 3. Update description as you learn more
xit edit 42 "!! Fix session timeout bug #urgent #security -> tomorrow"

# 4. Complete task
xit mark 42 --done

# 5. Verify completion
xit show --status checked
```

**Priority management:**

```bash
# Add without priority
xit add "Review documentation"

# Later escalate
xit show --show-id
xit prio 18 2

# After completion, clear priority
xit mark 18 --done
xit prio 18 0
```

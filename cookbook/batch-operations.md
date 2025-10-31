# Batch Operations

Most commands support multiple task IDs for efficient bulk operations.

## Batch mark - Update Multiple Tasks

```bash
# Mark multiple tasks as done
xit mark 2 3 4 5 6 --done

# Reopen several tasks
xit mark 8 9 10 --open

# Mark batch as ongoing
xit mark 15 16 17 --ongoing

# Archive completed sprint tasks
xit mark 20 21 22 23 24 --obsolete

# Flag multiple tasks for review
xit mark 30 31 32 --inquestion
```

**Efficient workflows:**

```bash
# Complete all tasks from a sprint
xit show --show-id
# Review output, note IDs: 5, 7, 9, 12, 15
xit mark 5 7 9 12 15 --done

# Mark all tasks in a feature as ongoing
xit mark 18 19 20 21 --ongoing
```

## Batch reschedule - Update Multiple Due Dates

```bash
# Move multiple tasks to same date
xit reschedule 2 3 4 --date 2025-12-31

# Postpone several tasks by one week
xit reschedule 5 6 7 8 --date "+1w"

# Set batch to today
xit reschedule 10 11 12 --date today

# Move sprint tasks to next sprint
xit reschedule 15 16 17 18 19 --date "+2w"
```

**Sprint management:**

```bash
# Show current sprint tasks
xit show --show-id

# Move incomplete tasks to next sprint
xit reschedule 3 5 8 12 --date "+2w"

# Set today's tasks
xit reschedule 20 21 22 --date today
```

## Batch rm - Delete Multiple Tasks

```bash
# Remove multiple tasks (with confirmation)
xit rm 2 3 4 5

# Force remove without confirmation
xit rm 2 3 4 5 --force

# Clean up completed tasks
xit show --status checked --show-id
# Note IDs, then:
xit rm 10 12 15 18 20 --force
```

**Cleanup workflows:**

```bash
# Archive old completed tasks
xit show --status checked --show-id
# Review and select IDs to remove
xit rm 5 8 12 15 18 22 25 --force

# Remove obsolete tasks
xit show --status obsolete --show-id
xit rm 3 7 9 --force

# Bulk cleanup
xit show --show-id  # Review all tasks
xit rm 1 2 3 4 5 6 7 8 9 10 --force
```

## Batch move - Relocate Multiple Tasks

```bash
# Move multiple tasks to archive
xit move 2 3 4 5 --target archive.xit

# Move completed tasks to done file
xit show --status checked --show-id
xit move 10 12 15 18 --target done.xit

# Organize by project
xit move 20 21 22 --target project-a.xit
xit move 25 26 27 --target project-b.xit
```

**Organization patterns:**

```bash
# Archive completed sprint
xit show --status checked --show-id
# Note: 8, 12, 15, 20, 23
xit move 8 12 15 20 23 --target sprint-12-done.xit

# Separate personal and work tasks
xit show --show-id
# Work tasks: 5, 7, 9, 11
xit move 5 7 9 11 --target work.xit
# Personal tasks: 6, 8, 10, 12
xit move 6 8 10 12 --target personal.xit
```

## Advanced batch workflows

**Sprint completion:**

```bash
# 1. Review sprint tasks
xit show --show-id

# 2. Mark completed tasks
xit mark 3 5 7 9 12 --done

# 3. Move incomplete to next sprint
xit reschedule 4 6 8 --date "+2w"

# 4. Archive completed work
xit move 3 5 7 9 12 --target sprint-15-done.xit

# 5. View remaining work
xit show --status open
```

**Project cleanup:**

```bash
# 1. Find all checked tasks
xit show --status checked --show-id

# 2. Archive to separate file
xit move 5 8 12 15 18 22 25 --target archive-2025.xit

# 3. Remove obsolete tasks
xit show --status obsolete --show-id
xit rm 2 4 6 --force

# 4. Verify cleanup
xit stats
```

**Task reorganization:**

```bash
# 1. List all tasks
xit show --show-id

# 2. Group by priority
# High priority → urgent.xit
xit show --show-id | rg '!!!'
xit move 3 7 12 --target urgent.xit

# Medium priority → important.xit
xit show --show-id | rg '!!'
xit move 5 9 15 --target important.xit

# 3. Verify organization
xit show --file urgent.xit
xit show --file important.xit
```

**Bulk rescheduling:**

```bash
# Postpone entire project by one month
xit show --show-id
xit reschedule 1 2 3 4 5 6 7 8 9 10 --date "+1m"

# Move week's tasks to today
xit reschedule 15 17 19 21 23 --date today

# Set deadline for milestone
xit reschedule 30 31 32 33 --date "2025-Q4"
```

## Tips for efficient batch operations

**Use show with --show-id:**

```bash
# Always get IDs first
xit show --show-id

# Filter and identify targets
xit show --status open --show-id
xit show --file work.xit --show-id
```

**Combine with grep/rg for filtering:**

```bash
# Find urgent tasks
xit show --show-id | rg '!!!'

# Find specific tags
xit show --show-id | rg '#urgent'

# Find due dates
xit show --show-id | rg '2025-11'
```

**Chain operations:**

```bash
# Mark done and move to archive
xit mark 5 7 9 --done
xit move 5 7 9 --target archive.xit

# Reschedule and mark ongoing
xit reschedule 12 15 18 --date today
xit mark 12 15 18 --ongoing
```

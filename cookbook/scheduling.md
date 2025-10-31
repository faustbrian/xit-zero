# Scheduling & Recurrence

## reschedule - Update Due Dates

Change or set due dates on existing tasks.

```bash
# Set specific date
xit reschedule 5 --date 2025-12-31

# Set to today
xit reschedule 5 --date today

# Set to tomorrow
xit reschedule 5 --date tomorrow

# Add relative time
xit reschedule 5 --date "+1w"    # One week from task's current date
xit reschedule 5 --date "+2m"    # Two months from current date
xit reschedule 5 --date "+3d"    # Three days from current date
```

**Date format options:**

| Format | Example | Description |
|--------|---------|-------------|
| `YYYY-MM-DD` | `2025-12-31` | Specific date |
| `today` | - | Current date |
| `tomorrow` | - | Next day |
| `+Nd` | `+3d` | N days from now |
| `+Nw` | `+2w` | N weeks from now |
| `+Nm` | `+1m` | N months from now |
| `+Ny` | `+1y` | N years from now |
| `YYYY-QN` | `2025-Q4` | Quarter (xit format) |
| `YYYY-WNN` | `2025-W45` | Week number (xit format) |

**Examples:**

```bash
# Postpone to end of year
xit reschedule 8 --date 2025-12-31

# Move to next sprint
xit reschedule 12 --date "+2w"

# Set for quarterly review
xit reschedule 15 --date "2025-Q4"

# Schedule for specific week
xit reschedule 20 --date "2025-W45"
```

## recur - Create Recurring Tasks

Generate multiple instances of a task at regular intervals.

```bash
# Create 4 weekly instances
xit recur 5 --interval 1w --count 4

# Create 5 bi-weekly instances
xit recur 3 --interval 2w --count 5

# Monthly recurrence for a year
xit recur 7 --interval 1m --count 12

# Daily tasks for 30 days
xit recur 2 --interval 1d --count 30

# Recur until specific end date
xit recur 10 --interval 1w --end 2026-12-31

# Save recurring tasks to different file
xit recur 5 --interval 1w --count 4 --target recurring.xit
```

**Interval options:**

| Interval | Example | Description |
|----------|---------|-------------|
| `Nd` | `1d`, `3d` | Every N days |
| `Nw` | `1w`, `2w` | Every N weeks |
| `Nm` | `1m`, `3m` | Every N months |
| `Ny` | `1y` | Every N years |

**Important notes:**
- Original task must have a due date
- Each instance gets the same description, priority, and tags
- Dates are calculated from the original task's due date
- Use `--count` OR `--end`, not both

**Common patterns:**

```bash
# Weekly team meeting (next 6 months = ~26 weeks)
xit add "Team standup #meetings -> 2025-11-04"
xit show --show-id  # Find ID
xit recur 42 --interval 1w --count 26

# Monthly review (rest of year)
xit add "! Monthly review #admin -> 2025-11-30"
xit recur 43 --interval 1m --end 2025-12-31

# Bi-weekly sprints
xit add "!! Sprint planning #team -> 2025-11-11"
xit recur 44 --interval 2w --count 12

# Daily standup for a month
xit add "Daily standup #team -> tomorrow"
xit recur 45 --interval 1d --count 30 --target daily.xit
```

## Scheduling workflows

**Project timeline:**

```bash
# Add milestones
xit add "!! Project kickoff #project -> 2025-11-01"
xit add "!! Sprint 1 complete #project -> 2025-11-15"
xit add "!! Sprint 2 complete #project -> 2025-11-29"
xit add "!! Final delivery #project -> 2025-12-13"

# View timeline
xit show --show-id | sort
```

**Recurring maintenance:**

```bash
# Weekly backup check
xit add "Verify backups #admin #weekly -> next-monday"
xit recur <id> --interval 1w --count 52

# Monthly security updates
xit add "! Run security updates #sysadmin -> 2025-11-01"
xit recur <id> --interval 1m --count 12
```

**Rescheduling patterns:**

```bash
# Postpone by one week
xit reschedule 5 --date "+1w"

# Move to end of quarter
xit reschedule 8 --date "2025-Q4"

# Batch reschedule for sprint change
xit reschedule 10 11 12 13 --date "+1w"
```

**Date arithmetic:**

```bash
# Task due in 3 days
xit add "Review PR #code -> +3d"

# Task due next month
xit add "Quarterly report #admin -> +1m"

# Push out by 2 weeks
xit reschedule 7 --date "+2w"
```

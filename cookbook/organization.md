# Organization & File Management

## move - Relocate Tasks Between Files

Move tasks from one `.xit` file to another.

```bash
# Move single task to different file
xit move 5 --target work.xit

# Move multiple tasks
xit move 2 3 4 --target archive.xit

# Create file if it doesn't exist
xit move 10 --target new-project.xit
```

**Organization strategies:**

```bash
# By status
xit move <done-ids> --target completed.xit
xit move <obsolete-ids> --target archive.xit

# By project
xit move <ids> --target project-alpha.xit
xit move <ids> --target project-beta.xit

# By time period
xit move <ids> --target 2025-Q4.xit
xit move <ids> --target december-tasks.xit

# By priority
xit move <critical-ids> --target urgent.xit
xit move <normal-ids> --target backlog.xit
```

## tag / untag - Add or Remove Tags

Manage task tags for categorization and filtering.

```bash
# Add tag to task
xit tag 5 urgent

# Remove tag from task
xit untag 5 urgent

# Add multiple tags (run multiple times)
xit tag 7 work
xit tag 7 meeting
xit tag 7 client

# Remove specific tag
xit untag 12 old-sprint
```

**Tagging conventions:**

```bash
# By type
#bug #feature #docs #refactor #test

# By priority/urgency
#urgent #critical #blocker #nice-to-have

# By project/area
#frontend #backend #api #database #devops

# By team/person
#team-alpha #team-beta #john #review-needed

# By time
#sprint-15 #q4 #2025 #this-week

# By status/workflow
#in-review #blocked #waiting #needs-info
```

**Examples:**

```bash
# Categorize a bug
xit add "Fix login timeout #bug"
xit show --show-id  # Get ID
xit tag 42 urgent
xit tag 42 backend

# Tag for review
xit tag 15 review-needed
xit tag 15 pull-request

# Sprint tagging
xit tag 20 sprint-16
xit tag 21 sprint-16
xit tag 22 sprint-16

# Remove outdated tags
xit untag 20 sprint-15
xit untag 21 sprint-15
```

## File discovery

The CLI automatically discovers `.xit` files in the current directory.

```bash
# Show all tasks from all .xit files in current dir
xit show

# Include subdirectories
xit show --subdir

# Work with specific file
xit show --file work.xit
xit stats --file personal.xit
```

**File organization patterns:**

### By context

```
current-dir/
├── work.xit       # Work tasks
├── personal.xit   # Personal tasks
├── ideas.xit      # Future ideas/notes
└── done.xit       # Completed archive
```

### By project

```
projects/
├── project-alpha.xit
├── project-beta.xit
├── maintenance.xit
└── archive/
    ├── 2024-completed.xit
    └── 2025-completed.xit
```

### By time period

```
tasks/
├── today.xit
├── this-week.xit
├── this-month.xit
├── backlog.xit
└── someday.xit
```

### By priority/status

```
organized/
├── urgent.xit        # Critical tasks
├── important.xit     # High priority
├── routine.xit       # Regular tasks
├── waiting.xit       # Blocked tasks
└── completed.xit     # Archive
```

## Complete organization workflow

**Daily workflow:**

```bash
# 1. Review all tasks
xit show --show-id

# 2. Check today's work
xit show --show-id | rg 'today\|$(date +%Y-%m-%d)'

# 3. Tag current work
xit tag 5 today
xit tag 8 today
xit tag 12 today

# 4. Mark tasks as you work
xit mark 5 --ongoing
# ... work on task ...
xit mark 5 --done

# 5. Archive completed
xit show --status checked --show-id
xit move 5 8 10 --target done.xit
```

**Weekly review:**

```bash
# 1. Check completion rate
xit stats

# 2. Archive completed tasks
xit show --status checked --show-id
xit move <ids> --target archive-$(date +%Y-W%V).xit

# 3. Clean up obsolete tasks
xit show --status obsolete --show-id
xit rm <ids> --force

# 4. Update tags for new sprint
xit show --show-id | rg 'sprint-15'
xit untag <ids> sprint-15
xit tag <ids> sprint-16

# 5. Reschedule as needed
xit show --show-id
xit reschedule <ids> --date "+1w"
```

**Project-based organization:**

```bash
# 1. Create project files
touch project-auth.xit project-payments.xit project-admin.xit

# 2. Move tasks to projects
xit show --show-id
xit move 5 7 9 --target project-auth.xit
xit move 12 15 18 --target project-payments.xit

# 3. Tag tasks within projects
xit show --file project-auth.xit --show-id
xit tag 5 backend
xit tag 7 frontend
xit tag 9 testing

# 4. Track project progress
xit stats --file project-auth.xit
xit stats --file project-payments.xit

# 5. Archive completed projects
xit move <all-done-ids> --target archive-auth-v1.xit
```

**Tag-based filtering workflow:**

```bash
# 1. Add comprehensive tags
xit show --show-id
xit tag 5 urgent backend api
xit tag 7 frontend ui bug
xit tag 9 docs review-needed

# 2. Filter by tag with grep
xit show --show-id | rg '#urgent'
xit show --show-id | rg '#backend'
xit show --show-id | rg '#review-needed'

# 3. Batch operations on tagged items
# Get IDs from grep output, then:
xit mark <urgent-ids> --ongoing
xit reschedule <review-ids> --date today

# 4. Clean up old tags
xit show --show-id | rg '#sprint-14'
xit untag <ids> sprint-14
```

## Tips

**File naming conventions:**

```bash
# Use descriptive names
work.xit          # Clear purpose
project-auth.xit  # Specific project
2025-Q4.xit      # Time-based

# Avoid generic names
tasks.xit        # Too vague if you have multiple files
todo.xit         # Doesn't convey context
```

**Tag best practices:**

- Use lowercase for consistency
- Use hyphens for multi-word tags: `#tech-debt`, `#code-review`
- Keep tags short but meaningful
- Establish tag conventions early
- Remove outdated tags regularly

**Finding tasks:**

```bash
# By tag
xit show --show-id | rg '#urgent'

# By due date
xit show --show-id | rg '2025-11'

# By priority
xit show --show-id | rg '!!!'

# By description keyword
xit show --show-id | rg -i 'authentication'
```

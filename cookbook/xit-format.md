# XIT Format Specification

The xit format is a plain-text specification for todos. This implementation follows the [xit v1.1 specification](https://github.com/jotaen/xit).

## Basic syntax

### Checkboxes (Status)

```
[ ] Open task
[x] Checked/completed task
[@] Ongoing task
[~] Obsolete task
[?] Task in question
```

**Status meanings:**

- `[ ]` **Open** - Task is pending, not started
- `[x]` **Checked** - Task is completed/done
- `[@]` **Ongoing** - Task is currently being worked on
- `[~]` **Obsolete** - Task is no longer relevant/cancelled
- `[?]` **In question** - Task needs clarification/blocked

### Priorities

Add exclamation marks before the description for priority levels:

```
[ ] ! Important task
[ ] !! Very important task
[ ] !!! Critical task
```

Priority levels:
- No `!` - Normal priority (default)
- `!` - Important (priority 1)
- `!!` - Very important (priority 2)
- `!!!` - Critical (priority 3)

### Tags

Tags start with `#` and can appear anywhere in the description:

```
[ ] Buy groceries #shopping #weekly
[ ] Fix auth bug #urgent #security #backend
[ ] Review PR #code-review #team-alpha
```

**Tag rules:**
- Tags can contain letters, numbers, hyphens, underscores
- Tags are case-sensitive (though lowercase is conventional)
- Multiple tags are supported per task
- Tags help with categorization and filtering

### Due dates

Due dates are specified with `->` followed by a date:

```
[ ] Submit report -> 2025-12-15
[ ] Sprint planning -> 2025-W45
[ ] Quarterly review -> 2025-Q4
```

**Supported date formats:**

| Format | Example | Description |
|--------|---------|-------------|
| `YYYY-MM-DD` | `2025-12-31` | Specific calendar date |
| `YYYY-Www` | `2025-W45` | ISO week number |
| `YYYY-Qq` | `2025-Q4` | Quarter (Q1-Q4) |

### Complete task syntax

All elements can be combined:

```
[x] !! Deploy hotfix #urgent #production -> 2025-11-01
[@] ! Review authentication PR #security #code-review -> 2025-W45
[ ] Update documentation #docs -> 2025-Q4
```

**Syntax order:**
1. Checkbox `[ ]`, `[x]`, `[@]`, `[~]`, `[?]`
2. Priority `!`, `!!`, `!!!` (optional)
3. Description (required)
4. Tags `#tag` (optional, multiple allowed)
5. Due date `-> date` (optional)

## Structure

### Groups

Tasks can be organized under headings:

```
Work Tasks
[ ] Review pull requests
[ ] Update documentation
[ ] Fix bugs

Personal
[ ] Buy groceries
[ ] Call dentist
```

**Group rules:**
- Any line without a checkbox is a group heading
- Groups are purely organizational
- Groups don't affect task behavior

### Continuation lines

Tasks can span multiple lines by indenting:

```
[ ] Complex task that requires
    a longer description and
    multiple lines to explain
    all the necessary details
```

**Continuation rules:**
- Lines must be indented (spaces or tabs)
- Continuation lines are part of the description
- Useful for detailed task descriptions

### Comments

Empty lines and lines starting with certain characters are ignored:

```
# This is a comment

Work Tasks
[ ] Review code

// Another comment style
[ ] Deploy changes

--- separator ---
[ ] More tasks
```

**Comment behavior:**
- Comments are preserved when reading files
- Comments don't affect task parsing
- Useful for documentation and organization

## Complete example

```
# Development Tasks - Sprint 15

High Priority
[x] !!! Fix critical security vulnerability #security #urgent -> 2025-11-01
[@] !! Implement user authentication #feature #backend -> 2025-11-05
[ ] !! Write API documentation #docs -> 2025-11-10

Regular Tasks
[ ] ! Refactor database queries #refactor #performance
    - Review current implementation
    - Optimize slow queries
    - Add proper indexing
[ ] Update UI components #frontend #ui -> 2025-W45
[ ] Add integration tests #testing #quality

Backlog
[ ] Research new caching strategy #research
[ ] Update dependencies #maintenance -> 2025-Q4
[~] Old feature request that's no longer needed #obsolete

Questions
[?] Clarify requirements for export feature #needs-info #client
```

## Specification compliance

This implementation is **fully compliant** with xit v1.1:

✅ All checkbox types (`[ ]`, `[x]`, `[@]`, `[~]`, `[?]`)
✅ Priority levels (1-3 exclamation marks)
✅ Tags (multiple `#tag` support)
✅ Due dates (date, week, quarter formats)
✅ Groups (task organization)
✅ Continuation lines (multi-line descriptions)
✅ Comments (preserved but ignored)

## Best practices

### File organization

```
# Use clear group headings
Today
[ ] Tasks for immediate attention

This Week
[ ] Tasks for the current week

Backlog
[ ] Future tasks and ideas
```

### Descriptive tasks

```
# Good - clear and actionable
[ ] Fix authentication timeout bug in login form #bug #urgent

# Less ideal - vague
[ ] Fix stuff
```

### Consistent tagging

```
# Establish tag conventions
#bug #feature #docs #refactor     # By type
#urgent #important #low-priority   # By urgency
#frontend #backend #devops         # By area
#sprint-15 #q4 #milestone-1       # By time/milestone
```

### Priority usage

```
# Reserve !!! for true emergencies
[x] !!! Production outage - database connection lost

# Use !! for important deadlines
[ ] !! Client demo preparation #client -> 2025-11-15

# Use ! for elevated tasks
[ ] ! Review security audit report #security

# Leave normal tasks unmarked
[ ] Update README #docs
```

### Date consistency

```
# Use specific dates for deadlines
[ ] Submit quarterly report -> 2025-12-31

# Use week numbers for sprint planning
[ ] Sprint review -> 2025-W45

# Use quarters for long-term goals
[ ] Complete v2.0 features -> 2025-Q4
```

## Format advantages

- **Plain text** - Works with any text editor
- **Version control friendly** - Easy to diff and merge
- **Human readable** - No special tools required
- **Grep-friendly** - Easy to search and filter
- **Future-proof** - Not tied to any specific tool
- **Portable** - Works across any platform

## Further reading

- Official xit specification: https://github.com/jotaen/xit
- This CLI's syntax differences: [cli-syntax.md](cli-syntax.md)

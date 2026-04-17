# Role Terminology Documentation

## ⚠️ IMPORTANT: Role Terminology Reversal

**This system uses REVERSED role terminology in the user interface.**

### Database vs. Display Terminology

In the **database**, roles are stored as:
- `'super_admin'` - Super administrators
- `'admin'` - Normal users (regular users)
- `'user'` - Administrators (system admins)

However, in the **user interface**, these are displayed as:
- `'super_admin'` → **"Super Admin"** (unchanged)
- `'admin'` → **"User"** (reversed - database 'admin' = UI 'User')
- `'user'` → **"Admin"** (reversed - database 'user' = UI 'Admin')

### Why This Matters

When working with roles in the codebase:

1. **Database Queries**: Use the actual database values (`'admin'`, `'user'`, `'super_admin'`)
2. **Display/UI**: Swap the labels for `'admin'` and `'user'` when showing to users
3. **Method Names**: 
   - `isRegularUser()` checks for `role === 'user'` (which is actually an "Admin" in UI terms)
   - `isAdmin()` checks for `role === 'admin'` (which is actually a "User" in UI terms)

### Files That Handle Role Display

The following files have been updated to correctly swap role labels:

1. **`resources/views/super-admin/roles/index.blade.php`**
   - Role display column swaps 'admin' → "User" and 'user' → "Admin"

2. **`resources/views/super-admin/analytics.blade.php`**
   - "By Role" section swaps labels correctly

3. **`resources/views/components/subscription-status.blade.php`**
   - Uses `isRegularUser()` to check for admins (role='user')
   - Admin actions are shown to users with `role='user'`

4. **`app/Http/Controllers/SuperAdmin/SubscriptionController.php`**
   - `renew()` method allows both super admins and regular admins (role='user')

5. **`routes/web.php`**
   - Admin subscription renewal route allows regular admins (role='user')

### Quick Reference

| Database Value | UI Display | Who They Are | Can Do |
|---------------|------------|--------------|--------|
| `'super_admin'` | Super Admin | System super administrators | Everything |
| `'admin'` | **User** | Normal/regular users | Standard features only |
| `'user'` | **Admin** | System administrators | Can manage subscriptions, make payments |

### When Adding New Features

**Always remember:**
- If checking for "Admin" users (who can manage subscriptions), use `isRegularUser()` or check `role === 'user'`
- If checking for "User" users (normal users), use `isAdmin()` or check `role === 'admin'`
- When displaying roles in UI, swap 'admin' → "User" and 'user' → "Admin"
- Super admin remains the same in both database and UI

### Example Code

```php
// ✅ CORRECT: Check if user is an Admin (can manage subscriptions)
if (auth()->user()->isRegularUser()) {
    // Show admin features
}

// ✅ CORRECT: Check if user is a normal User
if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
    // Show user features
}

// ✅ CORRECT: Display role in UI
@if($user->role == 'admin')
    <span>User</span>  {{-- Database 'admin' = UI "User" --}}
@elseif($user->role == 'user')
    <span>Admin</span>  {{-- Database 'user' = UI "Admin" --}}
@endif
```

---

**Last Updated**: 2025-01-XX
**Maintained By**: Development Team


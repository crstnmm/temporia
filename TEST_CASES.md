# Temporia — Integration Test Cases

Three required scenarios covering the full module integration.

---

## Test Case 1 — Diary: Create → Edit Same Day → Locked Next Day

### Setup
- Register or log in as `user_a@test.com`
- Navigate to today's date on the Temporia calendar

### Steps

#### Step 1 — Create entry
1. Click today's date cell → DateModal opens
2. Diary tab is active by default
3. Write content: `"Today I started using Temporia."`
4. Select mood: 😊 Happy
5. Click **Save entry**

**Expected:**
- ✅ Toast: "Diary entry saved ✨"
- ✅ Blue dot appears on today's calendar cell
- ✅ Entry renders in read mode with word count and time ago
- ✅ Stats bar "Diary entries" counter increments to 1

#### Step 2 — Edit same day
1. With modal still open, click **Edit entry**
2. Append to content: `" It has a great diary feature."`
3. Change mood to 🤩 Excited
4. Click **Update entry**

**Expected:**
- ✅ Toast: "Entry updated."
- ✅ Updated content visible immediately (no page reload)
- ✅ Mood badge updates to 🤩
- ✅ No duplicate API call — single PUT /api/diary-entries/{id}

#### Step 3 — Locked next day (simulate)

**Backend simulation** — run in tinker or set system clock:
```php
// In tinker: manually set entry date to yesterday
$entry = \App\Models\DiaryEntry::latest()->first();
$entry->update(['date' => now()->subDay()->toDateString()]);
```

Or navigate to yesterday's date if an entry exists there.

**Expected:**
- ✅ 🔒 amber banner: "This Temporia entry is locked after the day ends"
- ✅ "Edit entry" button is NOT rendered
- ✅ Entry content is visible in read-only mode
- ✅ "Delete entry" button IS still available (delete is always allowed)
- ✅ Attempting PUT via API returns 403: `{"message":"This Temporia entry is locked after the day ends.","locked":true}`

---

## Test Case 2 — Notes: Add → Visible on Calendar

### Setup
- Log in as `user_a@test.com`
- Navigate to any date (today or future)

### Steps

#### Step 1 — Add a note
1. Click a date cell → DateModal opens
2. Click **Notes** tab
3. Click **Add a Temporia note**
4. Fill in:
   - Title: `"Buy groceries"`
   - Body: `"Milk, eggs, bread"`
   - Color: amber
5. Click **Add note**

**Expected:**
- ✅ Toast: "Note added."
- ✅ Note card appears immediately in the Notes tab
- ✅ Notes tab badge shows `1`
- ✅ Amber dot appears on the calendar cell for that date
- ✅ Stats bar "Notes" counter increments

#### Step 2 — Add a second note same day
1. Click **Add a Temporia note** again
2. Fill in: Title `"Call dentist"`, Body `"Book for next week"`, Color: rose
3. Click **Add note**

**Expected:**
- ✅ Two note cards visible
- ✅ Notes tab badge shows `2`
- ✅ Calendar cell shows two amber dots (max 3 shown)

#### Step 3 — Navigate away and back
1. Click the **←** prev month button
2. Click the **→** next month button to return

**Expected:**
- ✅ Month data is fetched from cache (no new API call — check Network tab)
- ✅ Amber dots still visible on the correct date
- ✅ Clicking the date again shows both notes

#### Step 4 — Edit a note inline
1. Click the pencil icon on the first note
2. Change title to `"Buy groceries + fruit"`
3. Click **Save**

**Expected:**
- ✅ Toast: "Note updated."
- ✅ Updated title visible immediately

---

## Test Case 3 — Multiple Users: Strict Data Isolation

### Setup
- Register two accounts:
  - `user_a@test.com` / `PasswordA1`
  - `user_b@test.com` / `PasswordB1`

### Steps

#### Step 1 — User A creates data
1. Log in as User A
2. On today's date:
   - Create diary entry: `"User A's private diary"`
   - Add note: `"User A's private note"`
   - Set alert: `"User A's alert"` (High priority)
3. Note the IDs from the Network tab (e.g. diary entry id=1, note id=1, alert id=1)

**Expected:**
- ✅ All three items visible for User A
- ✅ Calendar shows all three dots (blue, amber, rose)

#### Step 2 — Log out User A
1. Click **Logout**

**Expected:**
- ✅ Toast: "Logged out successfully."
- ✅ Redirected to `/login`
- ✅ Calendar store is wiped (entries/notes/alerts arrays are empty)
- ✅ Token cleared from sessionStorage

#### Step 3 — Log in as User B
1. Log in as `user_b@test.com`

**Expected:**
- ✅ Toast: "Welcome back, [User B name]!"
- ✅ Calendar loads with 0 entries, 0 notes, 0 alerts
- ✅ No dots on any calendar cells
- ✅ Stats bar shows 0 / 0 / 0

#### Step 4 — User B attempts to access User A's resources directly

Test via browser DevTools console or curl:

```bash
# Get User B's token from sessionStorage in DevTools:
# sessionStorage.getItem('temporia_session')

# Attempt to read User A's diary entry
curl -H "Authorization: Bearer USER_B_TOKEN" \
  http://localhost:8000/api/diary-entries/1

# Attempt to update User A's diary entry
curl -X PUT \
  -H "Authorization: Bearer USER_B_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Hacked!"}' \
  http://localhost:8000/api/diary-entries/1

# Attempt to delete User A's note
curl -X DELETE \
  -H "Authorization: Bearer USER_B_TOKEN" \
  http://localhost:8000/api/notes/1
```

**Expected:**
- ✅ All three requests return `403 Forbidden`
- ✅ Response body: `{"message":"Forbidden."}`
- ✅ User A's data is completely unchanged
- ✅ No data leakage in response body

#### Step 5 — Verify index endpoints only return own data

```bash
curl -H "Authorization: Bearer USER_B_TOKEN" \
  "http://localhost:8000/api/diary-entries?year=2024&month=12"
```

**Expected:**
- ✅ Returns empty array `[]` (User B has no entries)
- ✅ User A's entries are NOT included

---

## Automated Verification Checklist

| Check | Method | Expected |
|-------|--------|----------|
| Diary lock enforced | PUT after date passes | 403 + `locked: true` |
| Only today's entry creatable | POST with past date | 422 |
| Duplicate entry blocked | POST twice same day | 409 |
| Note dot on calendar | Add note, check cell | Amber dot visible |
| Alert dot on calendar | Add alert, check cell | Rose dot visible |
| Cross-user read blocked | GET with other user's token | 403 |
| Cross-user write blocked | PUT with other user's token | 403 |
| Cross-user delete blocked | DELETE with other user's token | 403 |
| Store wiped on logout | Check Pinia devtools | All arrays empty |
| No redundant API calls | Check Network tab on nav | Cached months not re-fetched |
| Toast on every action | Perform any CRUD | Toast appears and auto-dismisses |
| Loading state on save | Click save, observe button | Spinner + disabled state |

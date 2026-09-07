<form action="/groups/apply/" method="post">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <input
        type="hidden"
        name="group_id"
        value="<?= (int) $group['group_id'] ?>"
    >

    <button
        class="apply-button"
        type="submit"
        aria-label="Apply to join <?= htmlspecialchars(
            $group['group_name'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >
        +
    </button>
</form>
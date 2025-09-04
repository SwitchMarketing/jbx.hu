<?php if($pager->getLastPageNumber() > 1): $pager->setSurroundCount(2) ?>
<div class="builty-pagination">
<nav class="pager" aria-label="Page navigation">
    <ul class="pagination">
    <?php if ($pager->hasPreviousPage()) : ?>
        <li class="page-item">
            <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>" class="page-link">
                <span aria-hidden="true"><i class='fa-solid fa-arrow-left-long'></i></span>
            </a>
        </li>
        <li class="page-item">
            <a href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>" class="page-link page-caret">
                <span aria-hidden="true"><i class='fa-solid fa-caret-left'></i></span>
            </a>
        </li>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <li class="page-item<?= $link['active'] ? ' active' : '' ?>">
            <a href="<?= $link['uri'] ?>" class="page-link">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach ?>

    <?php if ($pager->hasNextPage()) : ?>
        <li class="page-item">
            <a href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>" class="page-link page-caret">
                <span aria-hidden="true"><i class="fas fa-caret-right"></i></span>
            </a>
        </li>
        <li class="page-item">
            <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>" class="page-link">
                <span aria-hidden="true"><i class='fa-solid fa-arrow-right-long'></i></span>
            </a>
        </li>
    <?php endif ?>
    </ul>
</nav>
<?php endif; ?>
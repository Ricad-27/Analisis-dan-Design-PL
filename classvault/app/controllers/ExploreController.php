<?php
/**
 * ClassVault - ExploreController
 * Tahap 5: Jelajahi Dokumen Publik (/explore) dan File Dibagikan (/shared-documents).
 */

require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Security.php';

class ExploreController {

    // -------------------------------------------------------------------------
    //  GET /explore — dokumen publik, terbuka untuk semua termasuk tamu
    // -------------------------------------------------------------------------
    public function explore(): void {
        $doc      = new Document();
        $search   = Security::sanitize($_GET['q'] ?? '');
        $catId    = (int)($_GET['category'] ?? 0);
        $sort     = Security::sanitize($_GET['sort'] ?? 'date_desc');
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $allowed = ['date_desc','date_asc','name_asc','name_desc','size_desc','size_asc'];
        if (!in_array($sort, $allowed, true)) { $sort = 'date_desc'; }

        $result     = $doc->getPublicDocuments($search, $catId, $sort, $page);
        $categories = $doc->getCategories();

        View::render('explore/index', [
            'page_title' => 'Jelajahi Dokumen',
            'documents'  => $result['items'],
            'pagination' => $result,
            'categories' => $categories,
            'search'     => $search,
            'catId'      => $catId,
            'sort'       => $sort,
        ]);
    }

    // -------------------------------------------------------------------------
    //  GET /shared-documents — dokumen authenticated+public milik orang lain
    // -------------------------------------------------------------------------
    public function shared(): void {
        AuthMiddleware::requireAuth();

        $userId   = (int)Session::get('user_id');
        $doc      = new Document();
        $search   = Security::sanitize($_GET['q'] ?? '');
        $catId    = (int)($_GET['category'] ?? 0);
        $sort     = Security::sanitize($_GET['sort'] ?? 'date_desc');
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $allowed = ['date_desc','date_asc','name_asc','name_desc','size_desc','size_asc'];
        if (!in_array($sort, $allowed, true)) { $sort = 'date_desc'; }

        $result     = $doc->getSharedDocuments($userId, $search, $catId, $sort, $page);
        $categories = $doc->getCategories();

        View::render('explore/shared', [
            'page_title' => 'File Dibagikan',
            'documents'  => $result['items'],
            'pagination' => $result,
            'categories' => $categories,
            'search'     => $search,
            'catId'      => $catId,
            'sort'       => $sort,
        ]);
    }
}

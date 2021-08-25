<?php

namespace MijoraVenipak\Controller\Admin;

use PrestaShop\Module\LinkList\Core\Grid\LinkBlockGridFactory;
use PrestaShop\Module\LinkList\Repository\LinkBlockRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class VenipakBulkActionController extends FrameworkBundleAdminController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function bulkGenerateLabels(Request $request)
    {
        $orders = $request->request->get('order_orders_bulk');
        $moduleRepository = $this->get('prestashop.core.admin.module.repository');
        $module = $moduleRepository->getModule('mijoravenipak');
        $module_legacy = $module->getInstance();
        $module_legacy->bulkActionSendLabels($orders);
        return $this->redirectToRoute('admin_orders_index');
    }
}
<?php

namespace MijoraVenipak\Controller\Admin;
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

        $warehouse_groups = $module_legacy->formatWarehousesOrderGroups($orders);
        if(!empty($warehouse_groups))
        {
            foreach ($warehouse_groups as $warehouse_id => $orders)
            {
                $response = $module_legacy->bulkActionSendLabels(
                    [
                        'warehouse_id' => $warehouse_id,
                        'orders' => $orders
                    ]
                );
                if(isset($response['errors']))
                {
                    $this->flashErrors($response['errors']);
                }
                if(isset($response['success']))
                {
                    $this->addFlash('success', $response['success']);
                }
            }
        }
        else
        {
            $this->flashErrors("Could not group orders by warehouses.");
        }
        return $this->redirectToRoute('admin_orders_index');
    }


    /**
     * @param Request $request
     *
     * @return Response
     */
    public function bulkPrintLabels(Request $request)
    {
        $orders = $request->request->get('order_orders_bulk');
        $moduleRepository = $this->get('prestashop.core.admin.module.repository');
        $module = $moduleRepository->getModule('mijoravenipak');
        $module_legacy = $module->getInstance();
        $module_legacy->bulkActionPrintLabels($orders);
        return $this->redirectToRoute('admin_orders_index');
    }
}
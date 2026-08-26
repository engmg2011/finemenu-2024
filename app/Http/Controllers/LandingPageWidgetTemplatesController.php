<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\LandingPageWidgetTemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPageWidgetTemplatesController extends Controller
{
    public function __construct(private LandingPageWidgetTemplateRepositoryInterface $repository)
    {
    }

    /**
     * List all templates, optionally filtered by ?business_type=
     */
    public function index(Request $request): JsonResponse
    {
        $templates = $this->repository->listTemplates($request->query('business_type'));

        return response()->json(DataResource::collection($templates));
    }

    /**
     * Show a single template
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(new DataResource($this->repository->get($id)));
    }

    /**
     * Clone an existing widget as a public template
     * POST /landing-page-templates/clone-from-widget/{widgetId}
     * Body: { "name": "My Template" }
     */
    public function cloneFromWidget(Request $request, int $widgetId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $template = $this->repository->cloneFromWidget(
            $widgetId,
            $request->input('name'),
            $request->user()->id
        );

        return response()->json(new DataResource($template), 201);
    }

    /**
     * Clone a template into a business landing page as a new widget
     * POST /landing-page-templates/{templateId}/clone-to-widget
     * Body: { "landing_page_id": 5 }
     */
    public function cloneToWidget(Request $request, int $templateId): JsonResponse
    {
        $request->validate([
            'landing_page_id' => 'required|integer|exists:landing_pages,id',
        ]);

        $widget = $this->repository->cloneToWidget(
            $templateId,
            $request->input('landing_page_id')
        );

        return response()->json(new DataResource($widget), 201);
    }

    /**
     * Delete a template
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Template deleted successfully']);
    }
}

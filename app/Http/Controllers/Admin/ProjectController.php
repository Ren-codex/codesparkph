<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * List every project, published or not.
     */
    public function index(): Response
    {
        return Inertia::render('admin/Projects', [
            'projects' => Project::ordered()->get(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $this->withoutCoverFields($request);
        $data['position'] ??= (int) Project::max('position') + 1;

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('projects', 'public');
        }

        Project::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project added.')]);

        return to_route('admin.projects.index');
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $this->withoutCoverFields($request);

        if ($request->hasFile('cover')) {
            $this->deleteCover($project);
            $data['cover_path'] = $request->file('cover')->store('projects', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteCover($project);
            $data['cover_path'] = null;
        }

        $project->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project updated.')]);

        return to_route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->deleteCover($project);
        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project deleted.')]);

        return to_route('admin.projects.index');
    }

    /**
     * Validated attributes minus the upload-only fields, which are not columns.
     *
     * @return array<string, mixed>
     */
    private function withoutCoverFields(ProjectRequest $request): array
    {
        return collect($request->validated())
            ->except(['cover', 'remove_cover'])
            ->all();
    }

    private function deleteCover(Project $project): void
    {
        if ($project->cover_path) {
            Storage::disk('public')->delete($project->cover_path);
        }
    }
}

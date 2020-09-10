<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    public function index()
    {
        $domains = DB::table('domains')->get();
        $latestDomainChecks = DB::table('domain_checks')
            ->select()
            ->distinct('domain_id')
            ->orderBy('domain_id')
            ->orderByDesc('created_at')
            ->get();

        $latestCheckResults = $domains->map(function ($domain) use ($latestDomainChecks) {
            $domainCheck = $latestDomainChecks->where('domain_id', $domain->id)->first();
            return [
                'created_at' => optional($domainCheck)->created_at,
                'status_code' => optional($domainCheck)->status_code,
                'domain_id' => $domain->id
            ];
        });

        return view('domains.index', [
            'domains' => $domains,
            'checkResultsByDomain' => $latestCheckResults->keyBy('domain_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['domain.name' => 'required|url']);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                flash($message)->error();
            }
            return back()->withInput();
        }

        $normalizedUrl = normalizeUrl($request->input('domain.name'));
        $domain = DB::table('domains')->where('name', $normalizedUrl)->first();

        if ($domain) {
            flash('Url already exists')->info();
            return redirect()->route('domains.show', $domain->id);
        }

        $id = DB::table('domains')->insertGetId([
            'name' => $normalizedUrl,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        flash('Url has been added')->success();
        return redirect()->route('domains.show', $id);
    }

    public function show($id)
    {
        $domain = DB::table('domains')->where('id', $id)->first();
        if (!$domain) {
            abort(404);
        }
        $domainChecks = DB::table('domain_checks')
            ->where('domain_id', $id)
            ->orderByDesc('created_at')
            ->get();
        return view('domains.show', ['domain' => $domain, 'checks' => $domainChecks]);
    }
}

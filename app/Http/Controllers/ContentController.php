<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Cookie;

class ContentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function home(Request $request)
    {
        list(,$options) = $request->route();
        $forceFree = $options['as'] === 'airdrop';

        $sources = ['000', 'hostinger',];
        $canGetFreeTokens = $forceFree || in_array($request->get('utm_source'), $sources) || in_array($request->cookie('utm_source'), $sources);

        $response = response(view('pages.home', [
            'canGetFreeTokens' => $canGetFreeTokens,
            'email' => filter_var($request->get('email'), FILTER_VALIDATE_EMAIL) ? $request->get('email') : '',
        ])->render());

        if (in_array($request->get('utm_source'), $sources)) {
            $response->withCookie(new Cookie(
                'utm_source', $request->get('utm_source'), Carbon::now()->addYear()
            ));
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    function ico(Request $request) {
        if (env('APP_ENV', 'production') != 'local' && $request->get('key') != env('ICO_PREVIEW_KEY')) {
            abort(404);
        }

        $icoStart = Carbon::createFromTimestamp(env('ICO_STARTS_AT'));
        $icoEnd = Carbon::createFromTimestamp(env('ICO_ENDS_AT'));
        $icoDataAvailable = (bool) env('ICO_INFO_AVAILABLE', false);

        $showAddress = $icoDataAvailable && !!$request->cookie('participant', false);

        $raisedDecimals = 4;
        $raised = Cache::get('ico_balance', ['balance' => 0])['balance'] ?? 0;
        $raisedEth = bcdiv($raised, bcpow(10, 18 - $raisedDecimals)) / pow(10, $raisedDecimals);

        $hardCapEth = env('ICO_HARD_CAP');
        $softCapEth = env('ICO_SOFT_CAP');

        $progress = $raisedEth / $hardCapEth * 100;

        $icoAddress = env('ICO_ADDRESS');
        $icoRate = env('ICO_RATE');
        $totalSupply = env('TOKEN_SUPPLY');

        $countries = app()->make('countries');
        $blacklistedCountries = ['US', 'VI', 'UM', 'PR', 'AS', 'GU', 'MP', 'CN'];
        $currentCountry = false;

        try {
            $result = app()->make('geoip')->country($request->ip());

            if($result instanceof \GeoIp2\Model\Country) {
                $currentCountry = $result->country->isoCode;
            }
        }catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            //
        }


        if($request->get('key') == env('ICO_PREVIEW_KEY') && $request->has('mock')) {
            switch ($request->get('mock')) {
                case "pre_ico_addr_unavailable":
                case "pre_ico_addr_available":
                    $icoStart = Carbon::today()->addDay();
                    $icoEnd = $icoStart->copy()->addDay();
                    $icoDataAvailable = $request->get('mock') == 'pre_ico_addr_available';
                    break;
                case "ico_started_pre_softcap":
                case "ico_started_pre_hardcap":
                    $icoStart = Carbon::today()->subDay();
                    $icoEnd = Carbon::today()->addDays(2);
                    $raisedEth = ($request->get('mock') == "ico_started_pre_softcap" ? $softCapEth : $hardCapEth) / 2;
                    $icoDataAvailable = true;
                    break;
                case "ico_ended_pre_softcap":
                case "ico_ended_pre_hardcap":
                case "ico_ended_hardcap":
                    $icoStart = Carbon::today()->subDay();
                    $icoEnd = Carbon::today();
                    $raisedEth = ($request->get('mock') == "ico_ended_pre_softcap" ? $softCapEth : $hardCapEth) / 2;
                    $raisedEth = $request->get('mock') == "ico_ended_hardcap" ? $hardCapEth : $raisedEth;
                    break;
            }

            $showAddress = $icoDataAvailable && $request->has('participant');
            $progress = $raisedEth / $hardCapEth * 100;
        }

        return view('pages.ico', compact(
            'icoStart',
            'icoEnd',
            'raised',
            'icoAddress',
            'hardCapEth',
            'softCapEth',
            'raisedEth',
            'showAddress',
            'countries',
            'currentCountry',
            'blacklistedCountries',
            'raisedDecimals',
            'progress',
            'icoDataAvailable',
            'icoRate',
            'totalSupply'
        ));
    }

    /**
     * @return \Illuminate\View\View
     */
    function mvp() {
        $courses = app()->make('courses');
        return view('pages.mvp', compact('courses'));
    }

    /**
     * @return \Illuminate\View\View
     */
    function faq() {
        return view('pages.faq');
    }

    /**
     * @param $course
     * @param $lesson
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Laravel\Lumen\Http\Redirector
     */
    function lesson($course, $lesson) {
        $availableCourses = collect(app()->make('courses'))->keyBy('key');
        if($availableCourses->has($course)) {
            if($lesson != 'intro') {
                return redirect(route_lang('lesson', ['course' => $course, 'lesson' => 'intro']));
            }

            $hasLanding = file_exists(resource_path('views/pages/courses/'.$course.'/landing.blade.php'));

            return view('pages.courses.'.$course.'.lesson-'.$lesson, compact('course', 'lesson', 'hasLanding'));
        }

        abort(404);
    }

    /**
     * @param $course
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Laravel\Lumen\Http\Redirector
     */
    function course($course) {
        $availableCourses = collect(app()->make('courses'))->keyBy('key');
        if($availableCourses->has($course)) {
            if(!file_exists(resource_path('views/pages/courses/'.$course.'/landing.blade.php'))) {
                return redirect(route_lang('lesson', ['course' => $course, 'lesson' => 'intro']));
            }
            return view('pages.courses.'.$course.'.landing');
        }

        abort(404);
    }

    /**
     * @param $course
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    function redirectLanding($course) {
        $mappings = [
            'web-developer' => 'full-stack-web-developer',
            'smart-contract-developer' => 'smart-contracts',
        ];
        return redirect(route_lang('course', ['course' => $mappings[$course] ?? $course]), 301);
    }
}

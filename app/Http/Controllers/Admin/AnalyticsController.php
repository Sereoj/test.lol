<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Аналитика
 * @description Получение аналитических данных о пользователях, контенте и активности
 */
class AnalyticsController extends Controller
{
    /**
     * Получение аналитики по пользователям
     * 
     * Возвращает статистику по пользователям: регистрации, активность, демографические данные.
     *
     * @queryParam period string Период анализа (day, week, month, year). Example: month
     * @queryParam start_date date Начальная дата в формате Y-m-d. Example: 2025-01-01
     * @queryParam end_date date Конечная дата в формате Y-m-d. Example: 2025-03-30
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "total_users": 1250,
     *         "new_users": {
     *             "count": 156,
     *             "percent_change": 12.5
     *         },
     *         "active_users": {
     *             "daily": 450,
     *             "weekly": 750,
     *             "monthly": 950
     *         },
     *         "user_growth": [
     *             {"date": "2025-03-01", "count": 1100},
     *             {"date": "2025-03-15", "count": 1180},
     *             {"date": "2025-03-30", "count": 1250}
     *         ],
     *         "demographics": {
     *             "gender": {
     *                 "male": 55,
     *                 "female": 43,
     *                 "other": 2
     *             },
     *             "age_groups": {
     *                 "18-24": 25,
     *                 "25-34": 40,
     *                 "35-44": 20,
     *                 "45+": 15
     *             }
     *         },
     *         "top_locations": [
     *             {"name": "Москва", "count": 350},
     *             {"name": "Санкт-Петербург", "count": 230},
     *             {"name": "Новосибирск", "count": 120}
     *         ]
     *     }
     * }
     */
    public function users(Request $request)
    {
        // Здесь должен быть код для получения аналитики по пользователям
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => 1250,
                'new_users' => [
                    'count' => 156,
                    'percent_change' => 12.5
                ],
                'active_users' => [
                    'daily' => 450,
                    'weekly' => 750,
                    'monthly' => 950
                ],
                'user_growth' => [
                    ['date' => '2025-03-01', 'count' => 1100],
                    ['date' => '2025-03-15', 'count' => 1180],
                    ['date' => '2025-03-30', 'count' => 1250]
                ],
                'demographics' => [
                    'gender' => [
                        'male' => 55,
                        'female' => 43,
                        'other' => 2
                    ],
                    'age_groups' => [
                        '18-24' => 25,
                        '25-34' => 40,
                        '35-44' => 20,
                        '45+' => 15
                    ]
                ],
                'top_locations' => [
                    ['name' => 'Москва', 'count' => 350],
                    ['name' => 'Санкт-Петербург', 'count' => 230],
                    ['name' => 'Новосибирск', 'count' => 120]
                ]
            ]
        ]);
    }

    /**
     * Получение аналитики по постам
     * 
     * Возвращает статистику по постам: публикации, просмотры, лайки, комментарии.
     *
     * @queryParam period string Период анализа (day, week, month, year). Example: month
     * @queryParam start_date date Начальная дата в формате Y-m-d. Example: 2025-01-01
     * @queryParam end_date date Конечная дата в формате Y-m-d. Example: 2025-03-30
     * @queryParam category_id integer ID категории для фильтрации. Example: 2
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "total_posts": 3568,
     *         "new_posts": {
     *             "count": 156,
     *             "percent_change": 8.2
     *         },
     *         "post_metrics": {
     *             "views": 125890,
     *             "likes": 42560,
     *             "comments": 9845,
     *             "shares": 5230
     *         },
     *         "post_growth": [
     *             {"date": "2025-03-01", "count": 3400},
     *             {"date": "2025-03-15", "count": 3480},
     *             {"date": "2025-03-30", "count": 3568}
     *         ],
     *         "popular_categories": [
     *             {"name": "Технологии", "count": 980},
     *             {"name": "Бизнес", "count": 750},
     *             {"name": "Здоровье", "count": 480}
     *         ],
     *         "top_posts": [
     *             {
     *                 "id": 156,
     *                 "title": "Новый тренд в технологиях",
     *                 "views": 3560,
     *                 "likes": 1250,
     *                 "comments": 350
     *             }
     *         ]
     *     }
     * }
     */
    public function posts(Request $request)
    {
        // Здесь должен быть код для получения аналитики по постам
        return response()->json([
            'success' => true,
            'data' => [
                'total_posts' => 3568,
                'new_posts' => [
                    'count' => 156,
                    'percent_change' => 8.2
                ],
                'post_metrics' => [
                    'views' => 125890,
                    'likes' => 42560,
                    'comments' => 9845,
                    'shares' => 5230
                ],
                'post_growth' => [
                    ['date' => '2025-03-01', 'count' => 3400],
                    ['date' => '2025-03-15', 'count' => 3480],
                    ['date' => '2025-03-30', 'count' => 3568]
                ],
                'popular_categories' => [
                    ['name' => 'Технологии', 'count' => 980],
                    ['name' => 'Бизнес', 'count' => 750],
                    ['name' => 'Здоровье', 'count' => 480]
                ],
                'top_posts' => [
                    [
                        'id' => 156,
                        'title' => 'Новый тренд в технологиях',
                        'views' => 3560,
                        'likes' => 1250,
                        'comments' => 350
                    ]
                ]
            ]
        ]);
    }

    /**
     * Получение аналитики по доходам
     * 
     * Возвращает статистику по доходам: продажи, подписки, транзакции.
     *
     * @queryParam period string Период анализа (day, week, month, year). Example: month
     * @queryParam start_date date Начальная дата в формате Y-m-d. Example: 2025-01-01
     * @queryParam end_date date Конечная дата в формате Y-m-d. Example: 2025-03-30
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "total_revenue": {
     *             "amount": 250560,
     *             "currency": "RUB",
     *             "percent_change": 15.3
     *         },
     *         "revenue_breakdown": {
     *             "subscriptions": 125000,
     *             "one_time_purchases": 85560,
     *             "donations": 40000
     *         },
     *         "revenue_growth": [
     *             {"date": "2025-03-01", "amount": 200000},
     *             {"date": "2025-03-15", "amount": 225000},
     *             {"date": "2025-03-30", "amount": 250560}
     *         ],
     *         "top_products": [
     *             {
     *                 "id": 1,
     *                 "name": "Премиум подписка",
     *                 "revenue": 125000,
     *                 "sales": 250
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "Доступ к закрытому контенту",
     *                 "revenue": 85560,
     *                 "sales": 570
     *             }
     *         ],
     *         "payment_methods": [
     *             {"name": "Банковская карта", "percent": 75},
     *             {"name": "Электронный кошелек", "percent": 20},
     *             {"name": "Криптовалюта", "percent": 5}
     *         ]
     *     }
     * }
     */
    public function revenue(Request $request)
    {
        // Здесь должен быть код для получения аналитики по доходам
        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => [
                    'amount' => 250560,
                    'currency' => 'RUB',
                    'percent_change' => 15.3
                ],
                'revenue_breakdown' => [
                    'subscriptions' => 125000,
                    'one_time_purchases' => 85560,
                    'donations' => 40000
                ],
                'revenue_growth' => [
                    ['date' => '2025-03-01', 'amount' => 200000],
                    ['date' => '2025-03-15', 'amount' => 225000],
                    ['date' => '2025-03-30', 'amount' => 250560]
                ],
                'top_products' => [
                    [
                        'id' => 1,
                        'name' => 'Премиум подписка',
                        'revenue' => 125000,
                        'sales' => 250
                    ],
                    [
                        'id' => 2,
                        'name' => 'Доступ к закрытому контенту',
                        'revenue' => 85560,
                        'sales' => 570
                    ]
                ],
                'payment_methods' => [
                    ['name' => 'Банковская карта', 'percent' => 75],
                    ['name' => 'Электронный кошелек', 'percent' => 20],
                    ['name' => 'Криптовалюта', 'percent' => 5]
                ]
            ]
        ]);
    }

    /**
     * Получение аналитики по вовлеченности
     * 
     * Возвращает статистику по вовлеченности пользователей: активность, время на сайте, конверсии.
     *
     * @queryParam period string Период анализа (day, week, month, year). Example: month
     * @queryParam start_date date Начальная дата в формате Y-m-d. Example: 2025-01-01
     * @queryParam end_date date Конечная дата в формате Y-m-d. Example: 2025-03-30
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "engagement_metrics": {
     *             "active_users": 950,
     *             "avg_session_duration": 340,
     *             "avg_pages_per_session": 4.5,
     *             "bounce_rate": 35.2
     *         },
     *         "engagement_trends": [
     *             {"date": "2025-03-01", "active_users": 900, "avg_session_duration": 320},
     *             {"date": "2025-03-15", "active_users": 930, "avg_session_duration": 330},
     *             {"date": "2025-03-30", "active_users": 950, "avg_session_duration": 340}
     *         ],
     *         "user_activity": {
     *             "views": 125890,
     *             "likes": 42560,
     *             "comments": 9845,
     *             "shares": 5230
     *         },
     *         "retention": {
     *             "day_1": 80,
     *             "day_7": 65,
     *             "day_30": 45
     *         },
     *         "conversion_rates": {
     *             "registration": 12.5,
     *             "subscription": 8.2,
     *             "purchase": 3.5
     *         }
     *     }
     * }
     */
    public function engagement(Request $request)
    {
        // Здесь должен быть код для получения аналитики по вовлеченности
        return response()->json([
            'success' => true,
            'data' => [
                'engagement_metrics' => [
                    'active_users' => 950,
                    'avg_session_duration' => 340,
                    'avg_pages_per_session' => 4.5,
                    'bounce_rate' => 35.2
                ],
                'engagement_trends' => [
                    ['date' => '2025-03-01', 'active_users' => 900, 'avg_session_duration' => 320],
                    ['date' => '2025-03-15', 'active_users' => 930, 'avg_session_duration' => 330],
                    ['date' => '2025-03-30', 'active_users' => 950, 'avg_session_duration' => 340]
                ],
                'user_activity' => [
                    'views' => 125890,
                    'likes' => 42560,
                    'comments' => 9845,
                    'shares' => 5230
                ],
                'retention' => [
                    'day_1' => 80,
                    'day_7' => 65,
                    'day_30' => 45
                ],
                'conversion_rates' => [
                    'registration' => 12.5,
                    'subscription' => 8.2,
                    'purchase' => 3.5
                ]
            ]
        ]);
    }
} 
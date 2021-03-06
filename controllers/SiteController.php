<?php

namespace app\controllers;

use app\models\Article;
use app\models\Category;
use app\models\CommentForm;
use app\models\Tag;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $data = Article::getAll(5);
        $popular = Article::getPopular();
        $categories = Category::find()->all();


        return $this->render('index',
            [
                'articles' => $data['articles'],
                'pagination' => $data['pagination'],
                'popular' => $popular,
                'categories' => $categories,

            ]);
    }

    public function actionView($id)
    {
        $article = Article::findOne($id);
        $popular = Article::getPopular();
        $categories = Category::find()->all();
//        $tags = ArrayHelper::map(Tag::find()->all(), 'id', 'title');
        $tags = $article->tags;
        $comments = $article->getArticleComments();
        $commentForm = new CommentForm();

        $article->viewedCounter();
        echo ('gs');

        return $this->render('single', [
            'article' => $article,
            'popular' => $popular,
            'categories' => $categories,
            'tags' => $tags,
            'comments' => $comments,
            'commentForm' => $commentForm,
        ]);
    }

    public function actionCategory($id)
    {

        $data = Category::getArticlesByCategory($id);
        $popular = Article::getPopular();
        $categories = Category::find()->all();

        return $this->render('category', [
            'articles' => $data['articles'],
            'pagination' => $data['pagination'],
            'popular' => $popular,
            'categories' => $categories
        ]);
    }

    public function actionTag($id)
    {
        $data = Tag::getArticlesByTag($id);
        $popular = Article::getPopular();
        $categories = Category::getAll();
        $tags = Tag::getAll();
        return $this->render('tag', [
            'articles' => $data ['articles'],
            'pagination' => $data ['pagination'],
            'popular' => $popular,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionComment($id)
    {
        $model = new CommentForm();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->saveComment($id)) {
//                Yii::$app->getSession()->setFlash('comment', 'Your comment will be added soon!');
                return $this->redirect(['site/view', 'id' => $id]);
            }
        }
    }
}

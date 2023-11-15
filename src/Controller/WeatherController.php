<?php

namespace App\Controller;

use App\Entity\CityWeather;
use App\Entity\Notifications;
use App\Entity\User;
use App\Form\AddCityFormType;
use App\Form\SetTemperatureFormType;
use App\Service\WeatherManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenCage\Geocoder\Geocoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class WeatherController extends AbstractController
{

    #[Route('/',name: 'home')]
    public function home(EntityManagerInterface $entityManager,WeatherManager $weatherManager): Response
    {
        $cities = $entityManager->getRepository(CityWeather::class)->findAll();
        $weathers = '';
        if($cities){
            $cityJson = $weatherManager->locationJson($cities);
            $data = $weatherManager->weatherApi($cityJson);
            $weathers = $data['bulk'];
        }
        return $this->render('weather/home.html.twig', [
            'cities' => $cities,
            'weathers' => $weathers,
        ]);
    }

    #[Route('/addcity',name: 'add_city')]
    public function addCity(Request $request,EntityManagerInterface $entityManager)
    {
        $city = new CityWeather();
        $user = $entityManager->getRepository(User::class)->findOneBy(['id'=>$this->getUser()]);
        $form = $this->createForm(AddCityFormType::class, $city);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $city->setUser($user);
            $entityManager->persist($city);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('weather/addcity.html.twig',[
            'form' => $form->createView(),
            ]);
    }

    #[Route('/addcurrentlocation',name: 'add_current_location')]
    public function addCurrentLocation(Request $request,EntityManagerInterface $entityManager,WeatherManager $weatherManager)
    {
        if ($request->isXmlHttpRequest()){
            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');
            $openCodeApiKey = $this->getParameter('opencageapikey');
            $OCG = new Geocoder($openCodeApiKey);
            $city = $OCG->geocode($latitude.','.$longitude);
            $city = $city['results'][0]['components']['city'];
            $user = $entityManager->getRepository(User::class)->findOneBy(['id'=>$this->getUser()]);
            $dataExist = $entityManager->getRepository(CityWeather::class)->findOneBy(['user'=>$user,'cityname'=>$city]);
            if (!$dataExist){
                $newCity = new CityWeather();
                $newCity->setUser($user);
                $newCity->setCityname($city);
                $entityManager->persist($newCity);
                $entityManager->flush();
                return new JsonResponse(['data'=>true]);
            }
            return new JsonResponse(['data'=>false]);
        }
        return new Response('Failed',200);
    }

    #[Route('/deletecity/{id}',name: 'delete_city')]
    public function deleteCity($id,EntityManagerInterface $entityManager)
    {
        $city = $entityManager->getRepository(CityWeather::class)->findOneBy(['id'=>$id]);

        $entityManager->remove($city);
        $entityManager->flush();
        return $this->redirectToRoute('home');
    }

    #[Route('/settemperaturealert/{id}',name: 'set_temperature_alert')]
    public function setTemperatureAlert($id,Request $request,EntityManagerInterface $entityManager)
    {
        $city = $entityManager->getRepository(CityWeather::class)->findOneBy(['id'=>$id]);
        $form = $this->createForm(SetTemperatureFormType::class, $city);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $temp = $form["alerttemperature"]->getData();
            if (!is_float($temp)) {
                $temp = (float)$temp;
            }
            $city->setAlerttemperature($temp);
            $entityManager->persist($city);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('weather/set_temperature_alert.html.twig',[
            'form' => $form->createView()
            ]);
    }

    #[Route('/temperaturealert',name:'temperature_alert')]
    public function temperatureAlert(Request $request, EntityManagerInterface $entityManager,WeatherManager $weatherManager)
    {
        $cities = $entityManager->getRepository(CityWeather::class)->findTemperature();
        $user = $entityManager->getRepository(User::class)->findOneBy(['id'=>$this->getUser()]);
        if ($request->isXmlHttpRequest()){
            if($cities){
                $cityJson = $weatherManager->locationJson($cities);
                $data = $weatherManager->weatherApi($cityJson);
                $weathers = $data['bulk'];
                $temperatures = $weatherManager->temperatureAlert($cities,$weathers);
                if ($temperatures){
                    foreach ($temperatures as $temperature) {
                        $message = '';
                        if ( $temperature['alerttemperature'] < $temperature['temperature']) {
                            $message = $temperature['city'] .' temperature Increased to '. $temperature['temperature'].'°C';
                        }elseif ($temperature['alerttemperature'] > $temperature['temperature']){
                            $message = $temperature['city'] .' temperature Decreased to '. $temperature['temperature'].'°C';
                        }
                        $notification = new Notifications();
                        $notification->setMessage($message);
                        $notification->setUser($user);
                        $entityManager->persist($notification);
                        $entityManager->flush();
                    }

                    return new JsonResponse(['data'=>true]);
                }
                return new JsonResponse(['data'=>false]);
            }
        }
    }

    #[Route('/notifications',name:'notifications')]
    public function notifications(Request $request, EntityManagerInterface $entityManager)
    {
        $notifications = $entityManager->getRepository(Notifications::class)->getNotifications($this->getUser());
        return $this->render('weather/notifications.html.twig',[
            'notifications' => $notifications
        ]);
    }
    #[Route('/deletenotifications/{id}',name:'delete_notifications')]
    public function delete_Notifications($id, EntityManagerInterface $entityManager)
    {
        $notification = $entityManager->getRepository(Notifications::class)->findOneBy(['id'=>$id]);

        $entityManager->remove($notification);
        $entityManager->flush();
        return $this->redirectToRoute('notifications');
    }

}
<?php

    declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\IotCloud\Ys7\Provider;

    use Bailing\IotCloud\Ys7\AbstractProvider;

    class SettingProvider extends AbstractProvider
    {
        /**
         * 萤石云 设备布撤防
         * 对设备布撤防状态进行修改（活动检测开关），实现布防和撤防功能。
         *
         * @return mixed
         */
        public function setYsDeviceDefence(array $params)
        {
            return $this->post('/api/lapp/device/defence/set', $params);
        }

        /**
         * 萤石云 关闭设备视频加密.
         *
         * @return mixed
         */
        public function offYsDevicevideoEncrypt(array $params)
        {
            return $this->post('/api/lapp/device/encrypt/off', $params);
        }

        /**
         * 萤石云 开启设备视频加密.
         *
         * @return mixed
         */
        public function onYsDevicevideoEncrypt(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/encrypt/on', $params);
        }

        /**
         * 萤石云 获取wifi配置提示音开关状态..
         *
         * @return mixed
         */
        public function getYsSoundStatusOfWifi(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/sound/switch/status', $params);
        }

        /**
         * 萤石云 设置wifi配置提示音开关.
         * @param string $deviceSerial
         *
         * @return mixed
         */
        public function setYsSoundStatusOfWifi(array $params)
        {
            return $this->post('/api/lapp/device/sound/switch/set', $params);
        }

        /**
         * 获取镜头遮蔽开关
         * 萤石云 该接口用于获取设备镜头遮蔽开关状态（需要设备支持镜头遮蔽功能）.
         *
         * @return mixed
         */
        public function getYsDeviceSceneStatus(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/scene/switch/status', $params);
        }

        /**
         * 设置镜头遮蔽开关
         * 萤石云 该接口用于设置设备镜头遮蔽开关状态（需要设备支持镜头遮蔽功能）.
         *
         * @return mixed
         */
        public function setYsDeviceSceneStatus(array $params)
        {
            return $this->post('/api/lapp/device/scene/switch/set', $params);
        }

        /**
         * 萤石云 获取声源定位开关状态
         *
         * @return mixed
         */
        public function getYsDeviceSoundSrouceLocatedStatus(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/ssl/switch/status', $params);
        }

        /**
         * 萤石云 设置声源定位开关.
         *
         * @return mixed
         */
        public function setYsDeviceSoundSrouceLocatedStatus(array $params)
        {
            return $this->post('/api/lapp/device/ssl/switch/set', $params);
        }

        /**
         * 萤石云 获取设备布撤防时间计划.
         *
         * @return mixed
         */
        public function getYsDeviceDefencePlan(array $params)
        {
            return $this->post('/api/lapp/device/defence/plan/get', $params);
        }

        /**
         * 萤石云 设置布撤防时间计划.
         *
         * @return mixed
         */
        public function setYsDeviceDefencePlan(array $params)
        {
            return $this->post('/api/lapp/device/defence/plan/set', $params);
        }

        /**
         * 萤石云 获取摄像机指示灯开关状态
         *
         * @return mixed
         */
        public function getYsSameraLightStatus(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/light/switch/status', $params);
        }

        /**
         * 萤石云 设置摄像机指示灯开关.
         *
         * @return mixed
         */
        public function setYsSameraLightStatus(array $params)
        {
            return $this->post('/api/lapp/device/light/switch/set', $params);
        }

        /**
         * 萤石云 获取全天录像开关状态
         *
         * @return mixed
         */
        public function getYsFullDayRecordStatus(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/fullday/record/switch/status', $params);
        }

        /**
         * 萤石云 设置全天录像开关.
         *
         * @return mixed
         */
        public function setYsFullDayRecordStatus(array $params)
        {
            return $this->post('/api/lapp/device/fullday/record/switch/set', $params);
        }

        /**
         * 萤石云 获取移动侦测灵敏度配置.
         *
         * @return mixed
         */
        public function getYsDeviceAlgorithmConfig(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/algorithm/config/get', $params);
        }

        /**
         * 萤石云 设置移动侦测灵敏度.
         *
         * @return mixed
         */
        public function setYsDeviceAlgorithmConfig(array $params)
        {
            return $this->post('/api/lapp/device/algorithm/config/set', $params);
        }

        /**
         * 萤石云 设置告警声音模式.
         *
         * @return mixed
         */
        public function setYsDeviceAlarmSound(array $params)
        {
            return $this->post('/api/lapp/device/alarm/sound/set', $params);
        }

        /**
         * 萤石云 开启或关闭设备下线通知.
         *
         * @return mixed
         */
        public function updateYsDeviceNotify(array $params)
        {
            return $this->post('/api/lapp/device/notify/switch', $params);
        }

        /**
         * 萤石云 获取设备麦克风开关状态
         *
         * @return mixed
         */
        public function getYsDeviceCameraSound(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/camera/video/sound/status', $params);
        }

        /**
         * 萤石云 设置设备麦克风开关状态.
         *
         * @return mixed
         */
        public function setYsDeviceCameraSound(array $params)
        {
            return $this->post('/api/lapp/camera/video/sound/set', $params);
        }

        /**
         * 萤石云 设置设备移动跟踪开关.
         *
         * @return mixed
         */
        public function setYsDevceMove(array $params)
        {
            return $this->post('/api/lapp/device/mobile/status/set', $params);
        }

        /**
         * 萤石云 获取设备移动跟踪开关状态
         *
         * @return mixed
         */
        public function getYsDevceMove(string $deviceSerial)
        {
            $params = ['deviceSerial' => $deviceSerial];
            return $this->post('/api/lapp/device/mobile/status/get', $params);
        }

        /**
         * 萤石云 设置设备的osd名称.
         *
         * @return mixed
         */
        public function setYsDeviceOsdName(array $params)
        {
            return $this->post('/api/lapp/device/update/osd/name', $params);
        }

        /**
         * 萤石云 获取设备智能检测开关状态
         *
         * @return mixed
         */
        public function getYsDeviceIntelligenceDetection(array $params)
        {
            return $this->post('/api/lapp/device/intelligence/detection/switch/status', $params);
        }

        /**
         * 萤石云 设置设备智能检测开关状态
         *
         * @return mixed
         */
        public function setYsDeviceIntelligenceDetection(array $params)
        {
            return $this->post('/api/lapp/device/intelligence/detection/switch/set', $params);
        }
    }

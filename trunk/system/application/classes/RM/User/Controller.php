<?php
/**
* ResMania - Reservation System Framework http://resmania.com
* Copyright (C) 2011  ResMania Ltd.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*
*
* User end dispatcher
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_User_Controller extends RM_Controller {
    public function postDispatch() {
        parent::postDispatch();

        $config = new RM_Config();
        if ($config->getValue('rm_config_show_poweredby_logo')==='1'){
            $this->writePoweredByIcon();
        }
        
    }

    public function writePoweredByIcon(){
        echo '<div style="float: right;padding: 10px 20px 10px 10px;">
              <a href="http://resmania.com" target="_blank">
              <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFoAAAAUCAMAAAA6JNosAAAABGdBTUEAANbY1E9YMgAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAMAUExURfKNAev1/vKaKu7u7vSmNt3d3fLy8uXr8+y2afPVqunp6aWlpt10AOTayktLTfHRo+Li4va/emNkZeuDAEJERfTLlO1zAOff1O27c5iZmrm5ury9vfb29nl5eoWFhvF4AHx9fuzw9961e8HCwvn5+KChofjz6fv59vHFhomJiufLo+p8AFpbXO2tUDM0Nfbq2ez+/+qwXOjt9LGysjs7PdqLFq6ur+miPPDw8OOtXSorLePf2+aMFPKCAOXl5crKypWVlt7f4eanTJGRkmlpaqmqqoqPlebm5/X//yMjJRobHfbhwvr//+/Ae/z8/PTRndjY2PX6/rS0tHR1ddra2vr6+sbGxtTU1OGGA/P+//O7bfPZs7a3t9bW1lVWVv3+/vT09IGBggAAANibRfOSDu+JAHBwcs/Pz/JrAGxtbVBRUhESFI2NjvSdH/eiJ56en/P7/++MAe9uAKanp2prbdHQ0I+QkGZnaIqLjAsMDi8wMjc4Oh8gIVdYWVJSVBcZGh0eHywuL////0VGSA4QESgpKQwODwQFBgcICs3MzNPS0tLS0szMzJOUlMjHx5qbm1ZXWCYnJ6usrNPT07W1tV5fYOPHn/KYF+Pk5PT1+KSkpdyqY8PDxKysq1tsg+ShP9na2ueYKPGUEOuIBum/guOAAu2YHvjt3nyAhuDi5eTj5Of7/+nj2uSfO87Nzf7+/WBhYdrZ2f39/YaHh+Ln6umNCf7+/k9PUP38+v3//66vr/PYr/nVokhJSr+/v9vb3LO0tO3Yv/Hx8eZ6AHN0deGJDNfX1+7gzKOjo8vMzDU2N0BBQ//xy9LT03t7e+/Uq6ioqdPU1MPEw/n7/T0+P5ucnfTs4IKDhM3Nzf/Wk9jHrMzLy5KTlP/hr/Xm0ZeXmN7i6fjQnO+hMHV2d4uMjXh4eevh0Wp6jvz7+35/f3JzdIeJi5iYmNnZ2ueLBpWfruaVIueVKW9wcWN4kuuYIOuaLbW2t+fHltTT0/DLl9rUy/DGi/CPBOfDj+CEAP///0Qay68AAAEAdFJOU////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////wBT9wclAAAD+klEQVR42mL47953u5P6oI/1P8Oq9CaaANaNDDdCaWN0UxdDD4TBVj6L+7FUObfCfWkHZqoY3Q0z2qLq3EsRyaqXfC5zZR1BAsv9OTk5+aNVMPWc5bYHuSVmkgCGlNQuFVSje07YlxUnzrrwMubVLLl+6V4ukMxP7UJbW1thpwJ07ZOys1suNDWVd7ScckSXy04SQDV617kTV1ziNDfX5V/ZrKOw7z44QPgLzZWUlB6laqFrf1zf3ODSJGBVfdrTGF1unxkbqtEhU8KllL+FpU+ZHBQ+K/NpphRIhpPBcrVPpK/tEp+m5T99f15ralLn1/it8bvJraHkdFxTrOfUDRNPHtp8p7dCVaXJfnNZjqZyU9OUSU1hib3OICbE6G+ak04J6T09d+UqH5+LrMsRvmBgiHH+MYxs2inB8Ndn96LUranLju+U+MNQmLpU/71n2SuTJvuaCtnGGcpJLS1tLWVNddkNXpX135qqG5oSk7LbWjznQI2Wfmk84eXUMuda6Y4GlSlV9ipCwU1Nu5l0OTgM8xauVGOwdGRck2tulGf+i5dXvOl1e39FywLubG5ZT+Wm8LcCb3X4BD7xSbtLi2y3KD/NzNa3TqC4Jg1qdLGLanaWl0t5vYLXsabepF1NMpnGTfypKdpitqm3PATF5ktESRTabGHS1tXVPd9UkuTcn1QslLRvQtKUJmO5N3I1VSF1jRZNTVNPCwTzMTddmqJcoZcJNdq+X3lzvqarmaTehKam5iSRKazGkk3+Yh94n30V+94Upf1H+7C2sCmj9zthYWGxM8+TckqTzl1pSWhOCpBOAgEvVqsk96Ymqzau5oam4KQaTxM+BajRUnIxe2WznuTE6SU22bUD1bbeyW/aI7Y2solfzMZAtMjywSpra2v1nYxGBksybn5Ous6W5Nm+t8kk6djLpF0yXI3trHogoye2c1l1bGzJDmg6kJQFNfrE1DfZfF5CQl4TpwLTVvtUbq7tfE0PMz4WNKktLjrosVQ+l4VlunfkFyCVInZ3XlJmk2dSUlpTa9JFh6RqnaykjhDPpBlNTfVJ7hOT3PWS9tbqJZlAjZaxY5MBQpWmiqT9QN6mJq6kc02cPMnA3MLBM9vnntPRfxE80zyOLo34F5HctAJoan9SzbEmkSR7mYlAP1ZWzeDLBmYzk3qu5uymKUCRjo44pIwOAXpJdkByRvvEpqbL7Ebrm5oC2bl2NPkwGhmxz2xSZAdSjE0vmBOA9ss0NakwyzSFWrCxsVlsCj20DcT3U2F+0SQDFDrEhmF0/KmkU3GnklqZqVI8dfqhCBRbdVipUqlQZWWlTXHNFc7wP2Tdjy4EmHxsf0B4dxfFoLRrE0CAAQCACzjnYL3FgAAAAABJRU5ErkJggg==" alt="Powered By ResMania" />
              <div style="font-size: 9px; color: #a9a9a9;"><b>Powered By <span style="color: #FF6600;">Res</span><span style="color: #000;">Mania</span></b></div>
              </a></div><div style="clear: both;"></div>';
    }
}

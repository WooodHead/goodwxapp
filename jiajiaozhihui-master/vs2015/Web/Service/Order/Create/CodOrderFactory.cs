﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace Weixin.Service.Order
{
    public class CodOrderFactory:OrderFactory
    {
        public override IOrder Create(string openId)
        {
            return new CodOrder(openId);
        }
    }
}
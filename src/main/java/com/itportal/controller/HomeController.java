package com.itportal.controller;

import com.itportal.service.ProfessionService;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class HomeController {

    private final ProfessionService professionService;

    public HomeController(ProfessionService professionService) {
        this.professionService = professionService;
    }

    @GetMapping("/")
    public String home(Model model) {
        model.addAttribute("latestProfessions", professionService.getLatestProfessions(3));
        return "home";
    }
} 
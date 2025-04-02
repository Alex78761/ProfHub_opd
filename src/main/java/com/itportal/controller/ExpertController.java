package com.itportal.controller;

import com.itportal.model.Profession;
import com.itportal.service.ProfessionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

@Controller
@RequestMapping("/expert")
public class ExpertController {

    private final ProfessionService professionService;

    @Autowired
    public ExpertController(ProfessionService professionService) {
        this.professionService = professionService;
    }

    @GetMapping("/professions")
    public String listProfessions(Model model) {
        model.addAttribute("professions", professionService.getLatestProfessions(10));
        return "expert/professions";
    }

    @GetMapping("/professions/{id}/edit")
    public String editProfession(@PathVariable Long id, Model model) {
        model.addAttribute("profession", professionService.getProfessionById(id));
        return "expert/edit-profession";
    }

    @PostMapping("/professions/{id}")
    public String updateProfession(@PathVariable Long id, @ModelAttribute Profession profession) {
        profession.setId(id);
        professionService.saveProfession(profession);
        return "redirect:/expert/professions";
    }
} 